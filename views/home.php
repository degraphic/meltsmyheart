<h1>Hi there</h1>
<?php if(!empty($children)) { ?>
  <ol>
    <?php foreach($children as $child) { ?>
      <li><a href="/photos/source/<?php echo $child['c_id']; ?>"><?php echo $child['c_name']; ?></a></li>
    <?php } ?>
  </ol>
<?php } ?>
Click <a href="/child/new">here</a> to add a new child.
