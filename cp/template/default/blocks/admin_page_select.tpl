<div class="pagination">
    <ul>
        <?php if($page['first_url']) { ?>
            <li><a href="<?php echo $page['first_url']; ?>">&lt;&lt;</a></li>
        <?php } else { ?>
            <li class="disabled"><a href="#">&lt;&lt;</a></li>
        <?php } ?>
        <?php if($page['previous_url']) { ?>
            <li><a href="<?php echo $page['previous_url']; ?>">&lt;</a></li>
        <?php } else { ?>
            <li class="disabled"><a href="#">&lt;</a></li>
        <?php } ?>
        <?php foreach($page['page_numbers'] as $page_number) { ?>
        <?php if($page['current_page'] == $page_number['number']) { ?>
            <li class="disabled"><a href="#"><?php echo $page_number['number']; ?></a></li>
        <?php } else { ?>
            <li><a href="<?php echo $page_number['url']; ?>"><?php echo $page_number['number']; ?></a></li>
        <?php } ?>
        <?php } ?>
        <?php if($page['next_url']) { ?>
            <li><a href="<?php echo $page['next_url']; ?>"> &gt;</a></li>
        <?php } else { ?>
             <li class="disabled"><a href="#">&gt;</a></li>
        <?php } ?>
        <?php if($page['last_url']) { ?>
            <li><a href="<?php echo $page['last_url']; ?>"> &gt;&gt;</a></li>
        <?php } else { ?>
             <li class="disabled"><a href="#">&gt;&gt;</a></li>
        <?php } ?>
    </ul>
</div>