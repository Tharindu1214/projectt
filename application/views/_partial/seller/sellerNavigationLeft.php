<?php defined('SYSTEM_INIT') or die('Invalid Usage.'); ?>
<?php if( !empty( $seller_navigation_left ) ){ ?>
		<?php foreach( $seller_navigation_left as $nav ){ ?>
			<ul>
			<?php if( $nav['pages'] ){
                $getOrgUrl = (CONF_DEVELOPMENT_MODE) ? true : false;
				foreach( $nav['pages'] as $link ){ ?>
					<?php if( $link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_CMS && $link['nlink_cpage_id'] ): ?>
					<li><a target="<?php echo $link['nlink_target']; ?>" data-org-url="<?php echo CommonHelper::generateUrl('Cms','view',array($link['nlink_cpage_id']),'',null,false,$getOrgUrl); ?>" href="<?php echo CommonHelper::generateUrl('Cms','view', array($link['nlink_cpage_id'])); ?>"><?php echo $link['nlink_caption']; ?></a></li>
					<?php endif; ?>

					<?php if( $link['nlink_type'] == NavigationLinks::NAVLINK_TYPE_EXTERNAL_PAGE ):
					$url = str_replace('{SITEROOT}', CONF_WEBROOT_URL, $link['nlink_url']) ;
					$url = CommonHelper::processURLString( $url );
					?>
					<li><a target="<?php echo $link['nlink_target']; ?>" href="<?php echo $url; ?>"><?php echo $link['nlink_caption']; ?></a></li>
					<?php endif ?>
					<?php }
				} ?>
			</ul>
		<?php } ?>
	<?php
} ?>
