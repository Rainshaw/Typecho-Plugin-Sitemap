<?php

class Sitemap_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public function action()
    {
        $db = Typecho_Db::get();
        $options = Typecho_Widget::widget('Widget_Options');
        $select = $db->select()->from('table.contents')
            ->where('table.contents.type = ?', 'page')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.password IS NULL')
            ->where('table.contents.created < ?', $options->gmtTime)
            ->order('table.contents.created', Typecho_Db::SORT_DESC);
        $pages = $db->fetchAll($select);

        $select = $db->select()->from('table.contents')
            ->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.password IS NULL')
            ->where('table.contents.created < ?', $options->gmtTime)
            ->order('table.contents.created', Typecho_Db::SORT_DESC);
        $articles = $db->fetchAll($select);

        $select = $db->select()->from('table.metas')
            ->where('table.metas.type = ?', 'tag')
            ->order('table.metas.count', Typecho_Db::SORT_DESC);

        $tags = $db->fetchAll($select);

        $select = $db->select()->from('table.metas')
            ->where('table.metas.type = ?', 'category')
            ->order('table.metas.mid', Typecho_Db::SORT_ASC);

        $cates = $db->fetchAll($select);

        header("Content-Type: application/xml");
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<?xml-stylesheet type='text/xsl' href='" . $options->pluginUrl . "/Sitemap/sitemap.xsl'?>\n";
        echo "<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\nxsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\"\nxmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
        foreach ($pages as $page) {
            $type = $page['type'];
            $routeExists = (NULL != Typecho_Router::get($type));
            $page['pathinfo'] = $routeExists ? Typecho_Router::url($type, $page) : '#';
            $page['permalink'] = Typecho_Common::url($page['pathinfo'], $options->index);

            echo "\t<url>\n".
                "\t\t<loc>" . $page['permalink'] . "</loc>\n".
                "\t\t<lastmod>" . date('Y-m-d\TH:i:s\Z', $page['modified']) . "</lastmod>\n".
                "\t\t<changefreq>always</changefreq>\n".
                "\t\t<priority>0.7</priority>\n".
                "\t</url>\n";
        }
        foreach ($articles as $article) {
            $type = $article['type'];
            $article['categories'] = $db->fetchAll($db->select()->from('table.metas')
                ->join('table.relationships', 'table.relationships.mid = table.metas.mid')
                ->where('table.relationships.cid = ?', $article['cid'])
                ->where('table.metas.type = ?', 'category')
                ->order('table.metas.order', Typecho_Db::SORT_ASC));
            $article['category'] = urlencode(current(Typecho_Common::arrayFlatten($article['categories'], 'slug')));
            $article['slug'] = urlencode($article['slug']);
            $article['date'] = new Typecho_Date($article['created']);
            $article['year'] = $article['date']->year;
            $article['month'] = $article['date']->month;
            $article['day'] = $article['date']->day;
            $routeExists = (NULL != Typecho_Router::get($type));
            $article['pathinfo'] = $routeExists ? Typecho_Router::url($type, $article) : '#';
            $article['permalink'] = Typecho_Common::url($article['pathinfo'], $options->index);

            echo "\t<url>\n" .
                "\t\t<loc>" . $article['permalink'] . "</loc>\n" .
                "\t\t<lastmod>" . date('Y-m-d\TH:i:s\Z', $article['modified']) . "</lastmod>\n" .
                "\t\t<changefreq>always</changefreq>\n" .
                "\t\t<priority>0.5</priority>\n" .
                "\t</url>\n";
        }
        foreach ($tags as $tag) {
            $type = $tag['type'];
            $art_rs = $db->fetchRow($db->select()->from('table.contents')
                ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
                ->where('table.contents.status = ?', 'publish')
                ->where('table.relationships.mid = ?', $tag['mid'])
                ->order('table.contents.modified', Typecho_Db::SORT_DESC)
                ->limit(1));
            $routeExists = (NULL != Typecho_Router::get($type));
            $tag['pathinfo'] = $routeExists ? Typecho_Router::url($type, $tag) : '#';
            $tag['permalink'] = Typecho_Common::url($tag['pathinfo'], $options->index);

            echo "\t<url>\n" .
                "\t\t<loc>" . $tag['permalink'] . "</loc>\n" .
                "\t\t<lastmod>" . date('Y-m-d\TH:i:s\Z', $art_rs['modified']) . "</lastmod>\n" .
                "\t\t<changefreq>always</changefreq>\n" .
                "\t\t<priority>0.4</priority>\n" .
                "\t</url>\n";
        }

        foreach ($cates as $cate) {
            $type = $cate['type'];
            $art_rs = $db->fetchRow($db->select()->from('table.contents')
                ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
                ->where('table.contents.status = ?', 'publish')
                ->where('table.relationships.mid = ?', $cate['mid'])
                ->order('table.contents.modified', Typecho_Db::SORT_DESC)
                ->limit(1));

            $routeExists = (NULL != Typecho_Router::get($type));
            $cate['pathinfo'] = $routeExists ? Typecho_Router::url($type, $cate) : '#';
            $cate['permalink'] = Typecho_Common::url($cate['pathinfo'], $options->index);

            echo "\t<url>\n" .
                "\t\t<loc>" . $cate['permalink'] . "</loc>\n";
            if (empty($art_rs)) {
                echo "\t\t<lastmod>" . date('Y-m-d\TH:i:s\Z', time()) . "</lastmod>\n";
            } else {
                echo "\t\t<lastmod>" . date('Y-m-d\TH:i:s\Z', $art_rs['modified']) . "</lastmod>\n";
            }
            echo "\t\t<changefreq>always</changefreq>\n" .
                "\t\t<priority>0.4</priority>\n" .
                "\t</url>\n";
        }

        echo "</urlset>";
    }
}
