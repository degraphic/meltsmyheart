<?php
class Site
{
  public static function albumPhotosFacebook($childId, $albumId)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(empty($child))
      getRoute()->run('/error/404/ajax');
    $credential = Credential::getByService($userId, Credential::serviceFacebook);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    $photos = Facebook::getPhotos($userId, $childId, $credential['c_token'], $albumId);
    $markup = getTemplate()->get('photosList.php', array('childId' => $childId, 'child' => $child, 'photos' => $photos, 'ids' => $ids, 'service' => Credential::serviceFacebook));
    Api::success($markup);
  }

  public static function albumPhotosPhotagious($childId, $tag)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(empty($child))
      getRoute()->run('/error/404/ajax');
    $credential = Credential::getByService($userId, Credential::servicePhotagious);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    $photos = Photagious::getPhotos($userId, $childId, $credential['c_token'], $tag);
    $markup = getTemplate()->get('photosList.php', array('childId' => $childId, 'child' => $child, 'photos' => $photos, 'ids' => $ids, 'service' => Credential::servicePhotagious));
    Api::success($markup);
  }

  public static function albumPhotosSmugMug($childId, $albumId, $albumKey)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(empty($child))
      getRoute()->run('/error/404/ajax');
    $credential = Credential::getByService($userId, Credential::serviceSmugMug);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    $photos = SmugMug::getPhotos($userId, $childId, $credential['c_token'], $credential['c_secret'], $albumId, $albumKey);
    $markup = getTemplate()->get('photosList.php', array('childId' => $childId, 'child' => $child, 'photos' => $photos, 'ids' => $ids, 'service' => Credential::serviceSmugMug));
    Api::success($markup);
  }

  public static function albumsListFacebook($childId)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(empty($child))
      getRoute()->run('/error/404');
    $credential = Credential::getByService($userId, Credential::serviceFacebook);
    $albums = Facebook::getAlbums($childId, $credential['c_token'], $credential['c_uid']);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    getTemplate()->display('template.php', array('body' => 'albumsList.php', 'service' => Credential::serviceFacebook, 'albums' => $albums,
      'child' => $child, 'photoCount' => count($ids), 'js' => getTemplate()->get('javascript/albumsList.js.php', array('childId' => $childId, 'ids' => $ids))));
  }

  public static function albumsListPhotagious($childId)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(empty($child))
      getRoute()->run('/error/404');
    $credential = Credential::getByService($userId, Credential::servicePhotagious);
    $albums = Photagious::getAlbums($childId, $credential['c_token']);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    getTemplate()->display('template.php', array('body' => 'albumsList.php', 'service' => Credential::serviceFacebook, 'albums' => $albums,
      'child' => $child, 'photoCount' => count($ids), 'js' => getTemplate()->get('javascript/albumsList.js.php', array('childId' => $childId, 'ids' => $ids))));
  }

  public static function albumsListSmugMug($childId)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(empty($child))
      getRoute()->run('/error/404');
    $credential = Credential::getByService($userId, Credential::serviceSmugMug);
    getSmugMug()->setToken("id={$credential['c_token']}", "Secret={$credential['c_secret']}");
    $albums = SmugMug::getAlbums($childId, $credential['c_token'], $credential['c_secret'], $credential['c_uid']);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    getTemplate()->display('template.php', array('body' => 'albumsList.php', 'service' => Credential::serviceSmugMug, 'albums' => $albums,
      'child' => $child, 'photoCount' => count($ids), 'js' => getTemplate()->get('javascript/albumsList.js.php', array('childId' => $childId))));
  }

  public static function childCheck()
  {
    self::requireLogin();
    $value = $_POST['value'];
    if(preg_match('/^([a-zA-Z0-9-]+).meltsmyheart.com$/', $value, $matches))
      $value = $matches[1];
    if(preg_match('/^[a-zA-Z0-9-]$/', $value) === false)
      Api::success(false, "Invalid domain {$value}");

    $child = Child::getByDomain($value);
    Api::success(empty($child), "Checking if {$value} exists");
  }

  public static function childDelete($childId)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $child = Child::getById($userId, $childId);
    if(!$child)
    {
      if(isMobile())
        getRoute()->redirect('/?e=couldNotDeleteChild');
      else
        Api::forbidden('Sorry but we were unable to remove the child requested.');
    }

    Child::delete($userId, $childId);
    if(isMobile())
      getRoute()->redirect('/?m=childDeleted');
    else
      Api::success('Your child was successfully removed from our system.', array('childId' => $childId));
  }

  public static function childNew()
  {
    self::requireLogin();
    $children = Child::getByUserId(getSession()->get('userId'));
    if(count($children) >= Child::limitFree)
      self::requireUpgrade();

    $js = getTemplate()->get('javascript/formValidator.js.php', array('formId' => 'childNewForm'));
    getTemplate()->display('template.php', array('body' => 'childNew.php', 'r' => '/photos/source', 'js' => $js));
  }

  public static function childPage($name)
  {
    $child = Child::getByDomain($name);
    if(!$child)
      getRoute()->run('/error/404');

    $theme = Child::getTheme($child);
    $photos = Photo::getByChild($child['c_u_id'], $child['c_id']);
    $isOwner = getSession()->get('userId') == $child['c_u_id'];

    $params = array('theme' => $theme, 'child' => $child, 'photos' => $photos, 'isOwner' => $isOwner);
    if($isOwner)
      $params['js'] = getTemplate()->get('javascript/childPage.js.php');
    getTemplate()->display('page.php', $params);
  }

  public static function childPageCustomize($childId)
  {
    self::requireLogin();
    $child = Child::getById(getSession()->get('userId'), $childId);
    if(!$child)
      getRoute()->run('/error/404/ajax');
    $theme = isset($child['c_pageSettings']['theme']) ? $child['c_pageSettings']['theme'] : array();
    Api::success(getTemplate()->get('partials/childPageCustomize.php', array('child' => $child, 'theme' => $theme)));
  }

  public static function childPageCustomizePost($childId)
  {
    self::requireLogin();
    $child = Child::getById(getSession()->get('userId'), $childId);
    if(!$child)
      getRoute()->run('/error/404/ajax');

    $update = array();
    if(!empty($_POST['background']))
      $update[] = $_POST['background'];
    if(!empty($_POST['photo-layout']))
      $update[] = $_POST['photo-layout'];

    $settings = $child['c_pageSettings'];
    $settings['theme']['css'] = $update;
    Child::updateSettings(getSession()->get('userId'), $childId, $settings);

    $redirectUrl = isset($_POST['r']) ? $_POST['r'] : '/';
    getRoute()->redirect($redirectUrl, null, true);
  }

  public static function childPagePhoto($name, $photoId)
  {
    $child = Child::getByDomain($name);
    $isOwner = getSession()->get('userId') == $child['c_u_id'];
    if(!$child)
      getRoute()->run('/error/404');

    $theme = Child::getTheme($child);
    $photo = Photo::getById($child['c_u_id'], $photoId);

    $params = array('theme' => $theme, 'child' => $child, 'photo' => $photo, 'isOwner' => $isOwner);
    getTemplate()->display('page.php', $params);
  }

  public static function childNewPost()
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $date = strtotime(preg_replace('/(\d)(am|pm|AM|PM)/', '${1} ${2}', $_POST['childBirthDate']));
    $domain = $_POST['childDomain'];
    if(preg_match('/^([a-zA-Z0-9-]+)$/', $domain, $matches))
      $domain = $matches[1];

    if($date === false || empty($_POST['childName']) || empty($domain))
      getRoute()->redirect('/child/new?e=invalidFields');

    $childId = Child::add($userId, $_POST['childName'], $date, $_POST['childDomain']);
    Resque::enqueue('mmh_badge', 'Badger', array('childId' => $childId, 'userId' => $userId, 'badgeId' => Badge::getIdByTag('imnew')));
    $r = isset($_POST['r']) ? $_POST['r'] : '/photos/source';
    getRoute()->redirect("{$r}/{$childId}");
  }

  public static function connectFacebook($childId = null)
  {
    self::requireLogin();
    $credentialId = 0;
    if(isset($_GET['code']))
    {
      $fbRedirectUrl = getConfig()->get('urls')->base."/connect/facebook";
      if($childId)
        $fbRedirectUrl .= "/{$childId}";
      $resp = getFacebook()->fetchAccessToken($_GET['code'], $fbRedirectUrl);
      $token = $resp['access_token'];
      $profile = getFacebook()->api('/me', 'GET', array('access_token' => $token));
      if(empty($token) || !isset($profile['id']))
        throw new Exception('Could not get Facebook session', 500);
      $credentialId = Credential::add(getSession()->get('userId'), Credential::serviceFacebook, $token, null, $profile['id']);
    }

    if($credentialId)
    {
      $redirectUrl = getSession()->get('redirectUrl');
      if(empty($redirectUrl))
        $redirectUrl = "/albums/list/facebook/{$childId}";
      else
        getSession()->set('redirectUrl', null); // TODO implement removal
      getRoute()->redirect($redirectUrl);
    }
    else
    {
      getRoute()->run('/error/general');
    }
  }

  public static function connectPhotagious()
  {
    self::requireLogin();   
    $credentialId = 0;
    if(isset($_GET['key']))
    {
      $credentialId = Credential::add(getSession()->get('userId'), Credential::servicePhotagious, $_GET['key']);
    }

    if($credentialId)
    {
      $childId = getSession()->get('currentChildId');
      getRoute()->redirect("/albums/list/photagious/{$childId}");
    }
    else
    {
      getRoute()->redirect('/error/general?e=connectionFailed');
    }
  }

  public static function connectSmugMug()
  {
    self::requireLogin();   
    $credentialId = 0;
    if(isset($_GET['oauth_token']))
    {
      $smugReqTok = unserialize(getSession()->get('smugReqTok'));
      getSession()->set('smugReqTok', null); // TODO implement removal
      getSmugMug()->setToken("id={$smugReqTok['Token']['id']}", "Secret={$smugReqTok['Token']['Secret']}");
      $token = getSmugMug()->auth_getAccessToken();
      if(empty($token['Token']['id']))
        throw new Exception('Could not get SmugMug session', 500);
      $credentialId = Credential::add(getSession()->get('userId'), Credential::serviceSmugMug, $token['Token']['id'], $token['Token']['Secret'], $token['User']['id']);
    }
    if($credentialId)
    {
      $childId = getSession()->get('currentChildId');
      getSession()->set('currentChildId', null); // TODO implement removal
      $redirectUrl = getSession()->get('redirectUrl');
      if(empty($redirectUrl))
        $redirectUrl = "/albums/list/smugmug/{$childId}";
      getRoute()->redirect($redirectUrl);
    }
    else
    {
      getRoute()->run('/error/general');
    }
  }

  public static function error404($ajax = null)
  {
    getLogger()->info(sprintf('Error accessing %s on %s', $_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST']));
    if($ajax == 'ajax')
    {
      Api::notFound(getTemplate()->get('error404.php', array('page' => $_SERVER['REQUEST_URI'], 'ajax' => true)));
    }
    else
    {
      header('HTTP/1.0 404 Not Found');
      header('Status: 404 Not Found');
      getTemplate()->display('template.php', array('body' => 'error404.php', 'page' => $_SERVER['REQUEST_URI'], 'ajax' => false));
    }
    die();
  }

  public static function errorGeneral($ajax = false)
  {
    getTemplate()->display('template.php', array('body' => 'errorGeneral.php', 'page' => $_SERVER['REQUEST_URI']));
    die();
  }

  public static function forgot($confirm = null)
  {
    $js = getTemplate()->get('javascript/formValidator.js.php', array('formId' => 'forgotForm'));
    getTemplate()->display('template.php', array('body' => 'forgot.php', 'confirm' => $confirm, 'js' => $js));
  }

  public static function forgotPost()
  {
    $user = User::getByEmailAndPassword($_POST['email'], false);
    if(!$user)
      getRoute()->redirect('/forgot?e=emailDoesNotExist');

    $token = md5(str_repeat($_POST['email'], 2));
    Resque::enqueue('mmh_email', 'Email', array('subject' => 'Forgot your password?', 'email' => $_POST['email'], 'template' => getTemplate()->get('email/forgot.php', array('email' => $_POST['email'], 'token' => $token))));
    getRoute()->redirect('/forgot/confirm');
  }

  public static function home()
  {
    getTemplate()->display('mobile-only.php');
    die();
    $template = 'splash.php';
    $children = $js = null;
    if(User::isLoggedIn())
    {
      $userId = getSession()->get('userId');
      $template = 'home.php';
      $children = Child::getByUserId($userId);
      // TODO remove nested query
      foreach($children as $key => $value)
      {
        $children[$key]['photos'] = Photo::getByChild($userId, $value['c_id']);
      }
      $js = getTemplate()->get('javascript/home.js.php');
    }
    else
    {
      $js = getTemplate()->get('javascript/splash.js.php', array('host' => getConfig()->get('urls')->cdn));
    }
    getTemplate()->display('template.php', array('body' => $template, 'children' => $children, 'cntChildren' => count($children), 'js' => $js));
  }

  public static function join($context = null)
  {
    $params = array('body' => 'join.php', 'context' => quoteEncode($context), 'r' => (isset($_GET['r']) ? quoteEncode($_GET['r']) : ''),
      'js' => getTemplate()->get('javascript/formValidator.js.php', array('formId' => 'joinForm')));
    getTemplate()->display('template.php', $params);
  }
 
  public static function joinPost()
  {
    $redirectUrl = '/join?e=accountCreationError';
    if(empty($_POST['email']) || empty($_POST['password']))
      getRoute()->redirect($redirectUrl);
    elseif(User::getByEmailAndPassword($_POST['email'], $_POST['password']))
      getRoute()->redirect('/join?e=emailAlreadyExists');

    $userId = User::add($_POST['email'], $_POST['password']);
    if($userId)
    {
      if($affiliate = Affiliate::parseCookie())
        Affiliate::logUser(Affiliate::signup, $affiliate['userToken'], $affiliate['affiliateId']);
      $user = User::getById($userId);
      User::startSession($user);
      $redirectUrl = !empty($_POST['r']) ? $_POST['r'] : '/child/new';
      $args = array('subject' => 'Welcome to '.getConfig()->get('site')->name, 'email' => $user['u_email'], 'template' => getTemplate()->get('email/join.php', array('email' => $user['u_email'])));
      Resque::enqueue('mmh_email', 'Email', $args);
    }
    getRoute()->redirect($redirectUrl);
  }

  public static function login()
  {
    $r = isset($_GET['r']) ? quoteEncode($_GET['r']) : '/';
    $js = getTemplate()->get('javascript/formValidator.js.php', array('formId' => 'loginForm'));
    getTemplate()->display('template.php', array('body' => 'login.php', 'js' => $js, 'r' => $r));
  }

  public static function loginPost()
  {
    $redirectUrl = '/login?e=loginFailed&r=' . quoteDecode($_POST['r']);
    $user = User::getByEmailAndPassword($_POST['email'], $_POST['password']);
    if($user)
    {
      User::startSession($user);
      $redirectUrl = quoteDecode($_POST['r']);
    }
    getRoute()->redirect($redirectUrl);
  }

  public static function logout()
  {
    User::endSession();
    getRoute()->redirect('/');
  }

  public static function photoCustom($datePart, $fileName)
  {
    if($datePart && $fileName)
      $photoPath = Photo::generatePhoto($datePart, $fileName);
    if(!isset($photoPath) || empty($photoPath))
      getRoute()->run('/error/404'); 

    header('Content-Type: image/jpeg');
    readfile($photoPath);
  }

  public static function photoSelectAdd($childId, $photoId)
  {
    $userId = getSession()->get('userId');
    $internal = Photo::getById($userId, $photoId);
    if(!$internal)
      Api::error('There was a problem adding your photo. Please try again.');

    Photo::setUse($userId, $photoId, 1);
    $photo = $internal['p_meta'];

    // only queue if we haven't fetched the image already
    if(empty($internal['p_basePath']))
    {
      $args = array('userId' => $userId, 'childId' => $childId, 'entryId' => $photoId, 'photo' => $photo);
      Resque::enqueue('mmh_fetch', 'Fetcher', $args);
    }
    // return the opposite state (if adding return remove)
    $markup = getTemplate()->get('partials/photoSelectItemAction.php', array('action' => 'remove', 'childId' => $childId, 'photoId' => $photoId));
    Api::success($markup);
  }

  public static function photoSelectRemove($childId, $photoId)
  {
    $userId = getSession()->get('userId');
    $internal = Photo::getById($userId, $photoId);
    if(!$internal)
      Api::error('There was a problem removing your photo. Please try again.');

    Photo::setUse($userId, $photoId, 0);
    // return the opposite state (if removing return add)
    $markup = getTemplate()->get('partials/photoSelectItemAction.php', array('action' => 'add', 'childId' => $childId, 'photoId' => $photoId));
    Api::success($markup);
  }

  public static function photosSelectSmugMug($childId)
  {
    self::requireLogin();
    $userId = getSession()->get('userId');
    $credential = Credential::getByService($userId, Credential::serviceSmugMug);
    getSmugMug()->setToken("id={$credential['c_token']}", "Secret={$credential['c_secret']}");
    $albums = SmugMug::getAlbums($childId, $credential['c_token'], $credential['c_secret'], $credential['c_uid']);
    $ids = Photo::extractIds(Photo::getByChild($userId, $childId));
    getTemplate()->display('template.php', array('body' => 'photosSelect.php', 'service' => Credential::serviceSmugMug, 'albums' => $albums,
      'js' => getTemplate()->get('javascript/photoSelect.js.php', array('childId' => $childId, 'ids' => $ids))));
  }

  public static function photosSource($childId)
  {
    self::requireLogin();
    getSession()->set('currentChildId', $childId);
    $credentials = Credential::getByUserId(getSession()->get('userId'));
    foreach($credentials as $credential)
    {
      if($credential['c_service'] == Credential::serviceFacebook)
        $fbUrl = "/albums/list/facebook/{$childId}";
      if($credential['c_service'] == Credential::serviceSmugMug)
        $smugUrl = "/albums/list/smugmug/{$childId}";
      if($credential['c_service'] == Credential::servicePhotagious)
        $ptgUrl = "/albums/list/photagious/{$childId}";
    }
    if(!isset($fbUrl))
    {
      $fbUrl = getFacebook()->getAuthorizeUrl(
                  getConfig()->get('urls')->base."/connect/facebook/{$childId}",
                  array('scope' => getConfig()->get('thirdparty')->fb_perms)
                );
    }
    if(!isset($smugUrl))
    {
      $smugReqTok = getSmugMug()->auth_getRequestToken();
      getSession()->set('smugReqTok', serialize($smugReqTok));
      $smugUrl = getSmugMug()->authorize('Access=Full', 'Permissions=Read');
    }
    if(!isset($ptgUrl))
    {
      $ptgUrl = getConfig()->get('thirdparty')->ptg_host . '/?action=account.auth.act&callbackurl=' . urlencode(getConfig()->get('urls')->base . "/connect/photagious/{$childId}");
    }
    getTemplate()->display('template.php', array('body' => 'photosSource.php', 'fbUrl' => $fbUrl, 'smugUrl' => $smugUrl, 'ptgUrl' => $ptgUrl, 'childId' => $childId));
  }

  public static function photosAdd($childId)
  {
    self::requireLogin();
    $child = Child::getById(getSession()->get('userId'), $childId);
    if(!$child)
      getRoute()->run('/error/404');

    $js = getTemplate()->get('javascript/photosAdd.js.php', array('userId' => getSession()->get('userId'), 'childId' => $childId, 'child' => $child));
    getTemplate()->display('template.php', array('body' => 'photosAdd.php', 'child' => $child, 'js' => $js));
  }

  public static function photosAddPost($childId)
  {
    // swfupload doesn't send proper cookies
    // mobile doesn't send cookies either
    // self::requireLogin();
    $userId = self::requireUserCredentials($_POST);
    
    if(!isset($userId) || empty($userId))
    {
      header("HTTP/1.0 403 Forbidden");
      Api::forbidden('Could not authenticate user');
    }
    $destPath = getConfig()->get('paths')->photos.'/original/'.date('Ym');
    $destName = Uploader::safeName($_FILES['photo']['name']);
    $success = move_uploaded_file($_FILES['photo']['tmp_name'], "{$destPath}/{$destName}");
    if($success)
    {
      $key = Credential::serviceSelf . '-' . uniqid();
      $photoId = Photo::add($userId, $childId, $key);
      $args = array('entryId' => $photoId, 'userId' => $userId, 'childId' => $childId, 'photoPath' => "{$destPath}/{$destName}");
      Resque::enqueue('mmh_fetch', 'Uploader', $args);
      $user = User::getById($userId);
      $child = Child::getById($userId, $childId);
      $recipients = Recipient::getByUserId($userId);
      $baseName = str_replace('/original/','/base/',"{$destPath}/{$destName}");
      $attachment = array('source' => $baseName, 'name' => $_FILES['photo']['name'], 'type' => 'image/jpeg');
      $subject = !empty($_POST['message']) ? $_POST['message'] : sprintf('A new photo of %s', $child['c_name']);
      $template = getTemplate()->get('email/photo-posted.php', array('age' => $age));
      foreach($recipients as $recipient)
      {
        $email = $recipient['r_email'];
        $childName = ucwords(strtolower($child['c_name']));
        Resque::enqueue('mmh_email', 'EmailPhoto', array('subject' => $subject, 'userId' => $userId, 'childId' => $child['c_id'], 
          'entryId' => $photoId, 'email' => $email, 'from' => $user['u_email'], 'attachment' => $attachment, 'template' => $template));
      }

      $badgeId = Badge::getIdByTag('imnew');
      $doesUserHaveBadge = Badge::doesChildHave($badgeId, $child['c_id']);
      if(!$doesUserHaveBadge)
      {
        Resque::enqueue('mmh_badge', 'Badger', array('childId' => $child['c_id'], 'userId' => $userId, 'badgeId' => Badge::getIdByTag('imnew')));
      }
      elseif($userId && isset($_POST['userToken']) && rand(0,10) == 5) // mobile upload
      {
        $tokenInfo = User::getToken($userId, $_POST['userToken']);
        if(isAndroid($tokenInfo['ut_device']))
        {
          $badgeId = Badge::getIdByTag('android');
          $doesUserHaveBadge = Badge::doesChildHave($badgeId, $child['c_id']);
          if($doesUserHaveBadge)
            $badgeId = null;
        }
        elseif(isIos($tokenInfo['ut_device']))
        {
          $badgeId = Badge::getIdByTag('ios');
          $doesUserHaveBadge = Badge::doesChildHave($badgeId, $child['c_id']);
          if($doesUserHaveBadge)
            $badgeId = null;

        }
        if($badgeId !== null)
          Resque::enqueue('mmh_badge', 'Badger', array('childId' => $child['c_id'], 'userId' => $userId, 'badgeId' => $badgeId));
      }

      Api::success('Photo uploaded successfully', $args);
    }
    else
    {
      header("HTTP/1.0 502 Internal Server Error");
      Api::error('Could not upload image');
    }
  }

  public static function proxy($type, $service, $childId, $path)
  {
    $userId = getSession()->get('userId');
    $passThrough = null;
    $offDomain = false;
    switch($service)
    {
      case Credential::serviceFacebook:
        $offDomain = true;
        $credential = Credential::getByService($userId, Credential::serviceFacebook);
        $method = isset($_GET['method']) ? $_GET['method'] : null;
        switch($method)
        {
          default:
            $url = "https://graph.facebook.com/{$path}?access_token={$credential['c_token']}";
            break;
        }
        break;
    }

    if($type == 'r')
    {
      getRoute()->redirect($url, 301, $offDomain);
    }
    elseif($type == 'p')
    {
      if(!empty($passThrough))
      {
        echo $passThrough;
      }
      else
      {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
      }
    }
  }

  public static function reset($email, $token)
  {
    if($token != md5(str_repeat($email, 2)))
      getRoute()->run('/error/404');

    getTemplate()->display('template.php', array('body' => 'reset.php', 'email' => $email, 'token' => $token));
  }

  public static function resetPost($email, $token)
  {
    if($_POST['password'] !== $_POST['confirmpassword'])
      getRoute()->redirect("/reset/{$email}/{$token}?passwordmismatch=1");
    if($token != md5(str_repeat($email, 2)))
      getRoute()->run('/error/404');

    $user = User::getByEmailAndPassword($email, false);
    if(!$user)
      getRoute()->run('/error/404');

    User::password($user['u_id'], $user['u_email'], $_POST['password']);
    User::startSession($user);
    getRoute()->redirect('/');
  }

  public static function share()
  {
    self::requireLogin();
    $creds = Credential::getByService(getSession()->get('userId'), Credential::serviceFacebook);
    if(!$creds)
    {
      getSession()->set('redirectUrl', '/share');
      $fbUrl = getFacebook()->getAuthorizeUrl(
                  getConfig()->get('urls')->base."/connect/facebook",
                  array('scope' => getConfig()->get('thirdparty')->fb_perms)
                );
      getTemplate()->display('template.php', array('body' => 'shareConnect.php', 'js' => getTemplate()->get('javascript/shareConnect.php', array('fbUrl' => $fbUrl))));
    }
    else
    {
      $children = Child::getByUserId(getSession()->get('userId'));
      $js = getTemplate()->get('javascript/overlay.js.php');
      getTemplate()->display('template.php', array('body' => 'share.php', 'children' => $children, 'js' => $js));
    }
  }

  public static function shareFacebook($childId)
  { // require login simulated by 404 checks
    $userId = getSession()->get('userId');
    if(!$userId || !$childId)
      Api::error('No user or child specified');
    $credential = Credential::getByService($userId, Credential::serviceFacebook);
    if(!array_key_exists('c_uid', $credential) || empty($credential['c_uid']))
      Api::error('Could not find a credential');

    $child = Child::getById($userId, $childId);
    $photoUrl = getFacebookPhoto($credential['c_token']);
    Api::success(getTemplate()->get('shareFacebook.php', array('childId' => $childId, 'child' => $child, 'photoUrl' => $photoUrl)));
  }

  public static function shareFacebookPost($childId)
  { // require login simulated by 404 checks
    $userId = getSession()->get('userId');
    if(!$userId || !$childId)
      Api::error('No user or child specified');
    $credential = Credential::getByService($userId, Credential::serviceFacebook);
    if(!array_key_exists('c_uid', $credential) || empty($credential['c_uid']))
      Api::error('Could not find a credential');

    $fb = getFacebook();
    $child = Child::getById($userId, $childId);
    $credential = Credential::getByService($userId, Credential::serviceFacebook);
    try
    {
      $resp = $fb->api('/me/feed', 'POST', array('access_token' => $credential['c_token'], 'message' => getString('facebookStatus', $child), 
        'link' => getConfig()->get('urls')->base.Child::getPageUrl($child), 'name' => posessive($child['c_name']).' page on '.getConfig()->get('site')->name, 
        'caption' => getstring('facebookCaption'), 'description' => getstring('facebookDescription', $child), 'picture' => getConfig()->get('urls')->base.'/img/logo-square.png'));
      Api::success('Status updated successfully');
    }
    catch (FacebookApiException $e)
    {
      //getLogger()->warn($e->getMessage()); TODO
      error_log($e->getMessage());
      Api::error('Failed to update facebook status');
    }
  }

  public static function upgrade($action = null)
  {
    self::requireLogin();
    switch($action)
    {
      case 'cancel': // TODO: cancel view
        getTemplate()->display('template.php', array('body' => 'cancel.php'));
        break;
      case 'success':
        $userId = getSession()->get('userId');
        User::upgrade($userId);
        $user = User::getById($userId);
        if($affiliate = Affiliate::parseCookie())
        {
          Affiliate::logUser(Affiliate::upgrade, $affiliate['userToken'], $affiliate['affiliateId']);
          // affiliate email
          $_aff = Affiliate::getByKey($affiliate['affiliateKey']);
          error_log(var_export($affiliate, 1));
          error_log(var_export($_aff, 1));
          $_affUser = User::getById($_aff['a_u_id']);
         Resque::enqueue('mmh_email', 'Email', array('subject' => 'One of your referrals upgraded!', 
            'email' => $_affUser['u_email'], 'template' => getTemplate()->get('email/affiliate-upgrade.php')));
        }
        // upgrade welcome email
        Resque::enqueue('mmh_email', 'Email', array('subject' => 'Thank you for upgrading!', 
          'email' => getSession()->get('email'), 'template' => getTemplate()->get('email/upgraded.php')));
        User::startSession($user);
        getRoute()->redirect('/?m=upgraded');
        break;
      default:
        getTemplate()->display('template.php', array('body' => 'upgrade.php'));
        break;
    }
  }

  public static function requireLogin()
  {
    if(!getSession()->get('userId'))
    {
      if($_SERVER['REQUEST_METHOD'] == 'GET')
        $url = '/login?r='.urlencode($_SERVER['REQUEST_URI']);
      else
        $url = '/login';
      getRoute()->redirect($url);
    }
  }

  public static function requireUpgrade()
  {
    self::requireLogin();
    if(getSession()->get('accountType') != User::accountTypePaid)
    {
      getRoute()->run('/upgrade');
      die();
    }
  }

  public static function requireUserCredentials($post)
  {
    $userId = null;
    if(isset($post['usrhsh']))
    {
      $userId = User::postHash($post['usrhsh']);
    }
    elseif(isset($post['userId']) && isset($post['userToken']))
    {
      $userToken = User::checkToken($post['userId'], $post['userToken']);
      if($userToken)
        $userId = $post['userId'];
    }
    
    return $userId;
  }
}
