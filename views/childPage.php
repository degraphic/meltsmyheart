<div id="child" class="clearfix">
  <ul>
    <?php foreach($photos as $photo) { ?>
      <li>
        <div>
          <?php if(!empty($photo['p_basePath'])) { ?>
            <a href="<?php echo Photo::generateUrl($photo['p_basePath'], 800, 600, array(Photo::contrast)); ?>" class="child-photo" title="Taken on <?php echo date('l jS \of F \a\t g:i a', $photo['p_dateTaken']); ?> - <?php echo sprintf('%s old', displayAge($child['c_birthdate'], $photo['p_dateTaken'])); ?>" data-photo-id="<?php echo $photo['p_id']; ?>">
            <img src="<?php echo Photo::generateUrl($photo['p_basePath'], 200, 200, array(Photo::square, Photo::contrast)); ?>">
            </a>
            <label><?php echo sprintf('%s old', displayAge($child['c_birthdate'], $photo['p_dateTaken'])); ?></label>
          <?php } else { ?>
            <label>Processing...</label>
          <?php } ?>
        </div>
      </li>
    <?php } ?>
  </ul>
</div>
