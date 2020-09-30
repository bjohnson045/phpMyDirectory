<ul class="pagination pagination-sm">
    <li<?php if(!$page['first_url']) { ?> class="disabled"<?php } ?>><a href="<?php echo $page['first_url']; ?>">&lt;&lt;</a></li>
    <li<?php if(!$page['previous_url']) { ?> class="disabled"<?php } ?>><a href="<?php echo $page['previous_url']; ?>">&lt;</a></li>
    <?php foreach($page['page_numbers'] as $page_number) { ?>
        <li<?php if($page['current_page'] == $page_number['number']) { ?> class="active"<?php } ?>><a href="<?php echo $page_number['url']; ?>"><?php echo $page_number['number']; ?></a></li>
    <?php } ?>
    <li<?php if(!$page['next_url']) { ?> class="disabled"<?php } ?>><a href="<?php echo $page['next_url']; ?>"> &gt;</a></li>
    <li<?php if(!$page['last_url']) { ?> class="disabled"<?php } ?>><a href="<?php echo $page['last_url']; ?>"> &gt;&gt;</a></li>
</ul>