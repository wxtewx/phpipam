<?php

/**
 * Translation handler for JavaScript
 * Provides translation support for JavaScript files using PHP gettext
 */

// Include required functions
require_once( dirname(__FILE__) . '/functions.php' );

# Initialize database connection and user objects
$Database = new Database_PDO;
$User = new User ($Database);

# Set default language
if(isset($User->settings->defaultLang) && !is_null($User->settings->defaultLang) ) {
    # Get global default language
    $lang = $User->get_default_lang();
    if (is_object($lang))
        set_ui_language($lang->l_code);
}

// Handle translation request
if (isset($_GET['str'])) {
    header('Content-Type: text/plain');
    
    // Configure gettext
    bind_textdomain_codeset('phpipam', 'UTF-8');
    bindtextdomain("phpipam", dirname(__FILE__)."/locale");
    textdomain("phpipam");
    
    // Handle different translation functions
    if (isset($_GET['vars'])) {
        // 带变量的翻译（调用统一的 _() 函数处理）
        $vars = json_decode($_GET['vars'], true);
        print _($_GET['str'], ...$vars);  // 这里调用 PHP 端合并后的 _() 函数
    } else {
        // 纯文本翻译
        print _($_GET['str']);
    }
    exit();
}

// Return JavaScript translation functions
header('Content-Type: application/javascript');
?>
/**
 * 统一翻译函数：支持纯文本和带变量的翻译（与 PHP 端同步）
 * @param {string} str - 翻译文本（可包含 %s 等占位符）
 * @param {...*} vars - 可选参数，用于替换占位符的变量
 * @return {string} 翻译后的文本
 */
function _(str, ...vars) {
    // 构建请求数据
    const data = { str: str };
    // 如果有变量参数，序列化后传递
    if (vars.length > 0) {
        data.vars = JSON.stringify(vars);
    }
    // 同步请求翻译结果
    return $.ajax({
        url: "functions/js-translations.php",
        data: data,
        async: false
    }).responseText || str;
}

/**
 * Alias for _() (兼容旧代码中使用 gettext_() 的场景)
 */
function gettext_(str) {
    return _(str);
}

/**
 * 保留 tr_() 作为 _() 的别名（兼容旧代码）
 */
function tr_(str, ...vars) {
    return _(str, ...vars);
}