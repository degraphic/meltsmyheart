<h1>Your children</h1>

<?php if(count($children) > 0) { ?>
  <ul data-role="listview" data-split-icon="delete">
    <?php foreach($children as $child) { ?>
      <li>
        <img src="<?php echo $child['thumbnail']; ?>">
        <h3><a href="<?php echo Child::getPageUrl($child); ?>" rel="external"><?php echo $child['c_name']; ?></a></h3>
        <p>View <?php echo posessive($child['c_name']); ?> page</p>
        <a href="/child/confirm/delete/<?php echo $child['c_id']; ?>" data-rel="dialog">Delete</a>
      </li> 
    <?php } ?>
  </ul>
<?php } else { ?>
  button
<?php } ?>
