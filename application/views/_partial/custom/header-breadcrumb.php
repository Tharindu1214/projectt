<?php defined('SYSTEM_INIT') or die('Invalid usage'); ?>
<ul class="clearfix">
    <li><a href="<?php echo CommonHelper::generateUrl();?>"><?php echo Labels::getLabel('LBL_Home', $siteLangId);?> </a></li>
    <?php
    if (!empty($this->variables['nodes'])) {
        foreach ($this->variables['nodes'] as $nodes) {
            $short_title = (mb_strlen($nodes['title']) > 20) ? mb_substr($nodes['title'], 0, 20)."..." : $nodes['title']; ?>
            <?php if (!empty($nodes['href'])) {?>
                <li title="<?php echo $nodes['title']; ?>"><a href="<?php echo $nodes['href'];?>" <?php echo (!empty($nodes['other']))?$nodes['other']:'';?>><?php echo $short_title;?></a></li>
            <?php } else { ?>
                <li title="<?php echo $nodes['title']; ?>"><?php echo (isset($nodes['title'])) ? $short_title :'';?></li>
            <?php }
        }
    } ?>
</ul>
