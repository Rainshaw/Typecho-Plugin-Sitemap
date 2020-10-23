<?php
/**
 * <strong style="color:#28B7FF;font-family: 楷体;">生成Sitemap 符合Google和百度标准</strong>
 * 
 * @package Sitemap
 * @author <strong style="color:#28B7FF;font-family: 楷体;">Rainshaw</strong>
 * @version 2.0.0
 * @link https://github.com/RainshawGao
 */
class Sitemap_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     */
    public static function activate(){
	    Helper::addRoute('sitemap_route', '/sitemap.xml', 'Sitemap_Action', 'action');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     */
    public static function deactivate(){
	    Helper::removeRoute('sitemap_route');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

}
