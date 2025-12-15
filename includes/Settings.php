<?php
/**
 * 系统设置管理类
 * 用于从数据库中获取和管理系统设置
 */
class Settings {
    private static $settings = [];
    private static $loaded = false;
    
    /**
     * 从数据库加载所有设置
     */
    private static function loadSettings() {
        global $dbhost, $dbuser, $dbpass, $dbname;
        
        if (self::$loaded) {
            return;
        }
        
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
        if (!$conn) {
            // 如果数据库连接失败，使用默认值
            self::$settings = [
                'site_name' => '星跃短链接',
                'site_url' => $GLOBALS['my_url'] ?? 'https://localhost/'
            ];
            self::$loaded = true;
            return;
        }
        
        $result = mysqli_query($conn, "SELECT setting_key, setting_value FROM settings");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                self::$settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        
        // 设置默认值
        if (!isset(self::$settings['site_name'])) {
            self::$settings['site_name'] = '星跃短链接';
        }
        if (!isset(self::$settings['site_url'])) {
            self::$settings['site_url'] = $GLOBALS['my_url'] ?? 'https://localhost/';
        }
        
        mysqli_close($conn);
        self::$loaded = true;
    }
    
    /**
     * 获取指定设置的值
     * @param string $key 设置键名
     * @param mixed $default 默认值
     * @return mixed 设置值
     */
    public static function get($key, $default = null) {
        self::loadSettings();
        return isset(self::$settings[$key]) ? self::$settings[$key] : $default;
    }
    
    /**
     * 获取网站名称
     * @return string 网站名称
     */
    public static function getSiteName() {
        return self::get('site_name', '星跃短链接');
    }
    
    /**
     * 获取网站URL
     * @return string 网站URL
     */
    public static function getSiteUrl() {
        return self::get('site_url', $GLOBALS['my_url'] ?? 'https://localhost/');
    }
    
    /**
     * 重新加载设置（用于设置更新后）
     */
    public static function reload() {
        self::$settings = [];
        self::$loaded = false;
        self::loadSettings();
    }
}
?>