<?php
class Test
{
    public function getDesignerJsCssIncludeHtml($mergeFiles = true, $includeCommon = true)
    {
        $str = '';

        $use_root_url = '';

        $arrTpl = pathinfo(CONF_THEME_PATH . $this->renderingTpl);
        $fl = $arrTpl['dirname'] . DIRECTORY_SEPARATOR . 'page-css' . DIRECTORY_SEPARATOR . $arrTpl['filename'] . '.css';
        if (file_exists($fl)) {
            $this->addCss(substr($fl, strlen(CONF_THEME_PATH)));
        }
        $fl = $arrTpl['dirname'] . DIRECTORY_SEPARATOR . 'page-js' . DIRECTORY_SEPARATOR . $arrTpl['filename'] . '.js';
        if (file_exists($fl)) {
            $this->addJs(substr($fl, strlen(CONF_THEME_PATH)));
        }

        /* Include CSS */
        if ($includeCommon) {
            $pth = CONF_THEME_PATH . 'common-css';
            $last_updated = 0;

            $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);

            foreach ($arrCommonfiles as $fl) {
                if (! is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                    continue;
                }
                if ('.css' != substr($fl, - 4)) {
                    continue;
                }

                $time = filemtime($pth . DIRECTORY_SEPARATOR . $fl);
                if ($mergeFiles) {
                    $last_updated = max($last_updated, $time);
                } else {
                    if (CONF_DESIGNER_MODE) {
                        $str .= '<link rel="stylesheet" type="text/css"
							href="http://bwmarts.4demo.website/developer/css/'. $fl .  '" />' . "\n";
                    } else {
                        $str .= '<link rel="stylesheet" type="text/css"
							href="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'cssCommon', array(), $use_root_url, false) . '&f=' . rawurlencode($fl) . '&min=0&sid=' . $time, CONF_DEF_CACHE_TIME, '.css'). '" />' . "\n";
                    }
                }
            }

            if ($mergeFiles) {
                $str .= '<link rel="stylesheet" type="text/css"
						href="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'cssCommon', array(), $use_root_url, false) . '&min=1&sid=' . $last_updated, CONF_DEF_CACHE_TIME, '.css') . '" />' . "\n";
            }
        }

        /* Include JS Ends */

        return $str;
    }
    public function getAdminDesignerJsCssIncludeHtml($mergeFiles = true, $includeCommon = true)
    {
        $str = '';

        $use_root_url = '';

        /* $arrTpl = pathinfo(CONF_THEME_PATH . $this->renderingTpl);
        $fl = $arrTpl['dirname'] . DIRECTORY_SEPARATOR . 'page-css' . DIRECTORY_SEPARATOR . $arrTpl['filename'] . '.css';
        if ( file_exists($fl) ) {
        $this->addCss(substr($fl, strlen(CONF_THEME_PATH)));
        }
        $fl = $arrTpl['dirname'] . DIRECTORY_SEPARATOR . 'page-js' . DIRECTORY_SEPARATOR . $arrTpl['filename'] . '.js';
        if ( file_exists($fl) ) {
        $this->addJs(substr($fl, strlen(CONF_THEME_PATH)));
        } */

        /* Include CSS */
        if ($includeCommon) {
            $pth = CONF_THEME_PATH . 'common-css';
            $last_updated = 0;

            $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);

            foreach ($arrCommonfiles as $fl) {
                if (! is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                    continue;
                }
                if ('.css' != substr($fl, - 4)) {
                    continue;
                }

                $time = filemtime($pth . DIRECTORY_SEPARATOR . $fl);
                if ($mergeFiles) {
                    $last_updated = max($last_updated, $time);
                } else {
                    if (CONF_DESIGNER_MODE) {
                        $str .= '<link rel="stylesheet" type="text/css"
							href="http://bwmarts.4demo.website/developer/admin/css/'. $fl .  '" />' . "\n";
                    } else {
                        $str .= '<link rel="stylesheet" type="text/css"
							href="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'cssCommon', array(), $use_root_url, false) . '&f=' . rawurlencode($fl) . '&min=0&sid=' . $time, CONF_DEF_CACHE_TIME, '.css'). '" />' . "\n";
                    }
                }
            }

            if ($mergeFiles) {
                $str .= '<link rel="stylesheet" type="text/css"
						href="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'cssCommon', array(), $use_root_url, false) . '&min=1&sid=' . $last_updated, CONF_DEF_CACHE_TIME, '.css') . '" />' . "\n";
            }
        }

        /* Include JS */
        $str .= '<script type="text/javascript">
				var siteConstants = ' . json_encode(
            array(
            'webroot' => CONF_WEBROOT_URL,
            'webroot_traditional' => CONF_WEBROOT_URL_TRADITIONAL,
            'rewritingEnabled' => (CONF_URL_REWRITING_ENABLED ? '1' : '0'),
            )
        ) . ';
	    	</script>' . "\r\n";

        if ($includeCommon) {
            $pth = CONF_THEME_PATH . 'common-js';
            //$dir = opendir($pth);
            $last_updated = 0;

            $arrCommonfiles = scandir($pth, SCANDIR_SORT_ASCENDING);

            foreach ($arrCommonfiles as $fl) {
                if (!is_file($pth . DIRECTORY_SEPARATOR . $fl)) {
                    continue;
                }
                if ('.js' != substr($fl, -3)) {
                    continue;
                }
                if ('noinc-' == substr($fl, 0, 6)) {
                    continue;
                }

                $time = filemtime($pth . DIRECTORY_SEPARATOR . $fl);

                if (file_exists(CONF_CORE_LIB_PATH . 'js' . DIRECTORY_SEPARATOR . $fl)) {
                    $time = filemtime(CONF_CORE_LIB_PATH . 'js' . DIRECTORY_SEPARATOR . $fl);
                }

                if ($mergeFiles) {
                    $last_updated = max($last_updated, $time);
                } else {
                    $str .= '<script type="text/javascript" language="javascript"
							src="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'jsCommon', array(), $use_root_url, false) . '&f=' . rawurlencode($fl) . '&min=0&sid=' . $time, CONF_DEF_CACHE_TIME, '.css') . '"></script>' . "\n";
                }
            }

            if ($mergeFiles) {
                $str .= '<script type="text/javascript" language="javascript"
							src="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'jsCommon', array(), $use_root_url, false) . '&min=0&sid=' . $last_updated, CONF_DEF_CACHE_TIME, '.css'). '"></script>' . "\n";
            }
        }
        if (count($this->arr_page_js) > 0) {
            $last_updated = 0;
            foreach ($this->arr_page_js as $val) {
                $time = filemtime(CONF_THEME_PATH. $val);
                if ($mergeFiles) {
                    $last_updated = max($last_updated, $time);
                } else {
                    $str .= '<script type="text/javascript" language="javascript"
							src="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode($val) . '&min=0&sid=' . $time, CONF_DEF_CACHE_TIME, '.css'). '" ></script>' . "\n";
                }
            }
            if ($mergeFiles) {
                $str .= '<script type="text/javascript" language="javascript"
						src="' . FatCache::getCachedUrl(CommonHelper::generateUrl('JsCss', 'js', array(), $use_root_url, false) . '&f=' . rawurlencode(implode(',', $this->arr_page_js)) . '&min=1&sid=' . $last_updated, CONF_DEF_CACHE_TIME, '.css'). '" ></script>' . "\n";
            }
        }
        /* Include JS Ends */

        return $str;
    }
}
