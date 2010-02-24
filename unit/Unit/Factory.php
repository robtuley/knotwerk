<?php
/**
 * Unit testing factory.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates unit test settings.
 *
 * @package unit
 */
class T_Unit_Factory
{

    /**
     * XML configuration.
     *
     * @var T_Xml_Element
     */
    protected $xml;

    /**
     * Pooled Mysql/Postgres connections.
     */
    protected $mysql = null;
    protected $pgsql = null;

    /**
     * Load config file.
     */
    function __construct()
    {
        $config = T_ROOT_DIR.'unit.xml';
        if (!is_file($config)) {
            $template = T_ROOT_DIR.'unit/template.xml';
            if (!is_file($template)) {
                $msg = "$config does not exist, and no template $template";
                throw new Exception($msg);
            }
            copy($template,$config);
        }
        $xml = file_get_contents(T_ROOT_DIR.'unit.xml');
        $this->xml = new SimpleXMLElement($xml);
    }

    /**
     * Whether network (i.e. internet connected) is available.
     *
     * @return bool
     */
    function isNetwork()
    {
        return ((string)$this->xml->network['enabled'])=='yes';
    }

    /**
     * Get available db connections.
     *
     * @return T_Db[]
     */
    function getAllDb()
    {
        $dbs = array();
        $dbs['.lite.sql'] = new T_Pdo_Single(
                new T_Sqlite_Connection(':memory:')   );
        if ($mysql=$this->getMysqlConnection()) {
            $dbs['.my.sql'] = new T_Pdo_Single($mysql);
        }
        if ($pgsql=$this->getPostgresConnection()) {
            $dbs['.pg.sql'] = new T_Pdo_Single($pgsql);
        }
        return $dbs;
    }

    /**
     * Gets mysql connection if available.
     *
     * @return T_Mysql_Connection
     */
    function getMysqlConnection()
    {
        if (!is_null($this->mysql)) return $this->mysql;
        $data = array();
        $data['host']    = (string) $this->xml->mysql->host;
        $data['user']    = (string) $this->xml->mysql->user;
        $data['passwd']  = (string) $this->xml->mysql->password;
        $data['name']    = (string) $this->xml->mysql->name;
        $data['port']    = (string) $this->xml->mysql->port;
        if (strlen($data['port'])==0) $data['port'] = null;

        if (array_sum(array_map('strlen',$data))>0) {
            $this->mysql = new T_Mysql_Connection($data['host'],
                                           $data['user'],
                                           $data['passwd'],
                                           $data['name'],
                                           $data['port']);
        } else {
            $this->mysql = false;
        }

        if ($this->mysql) {

            // now try to connect to possible
            try {
                $pdo = $this->mysql->connect();
            } catch (T_Exception_Db $e) {
                $msg = "Connection to test db failed (".
                       $e->getMessage().
                       "). Check parameters in unit.xml config.";
                throw new RuntimeException($msg);
            }

            // drop any existing tables
            $tables = $pdo->query('SHOW TABLES')->fetchAll();
            if (count($tables)>0) {
                $drop = array();
                foreach ($tables as $row) $drop[] = current($row);
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                $sql = 'DROP TABLE `'.implode('`,`',$drop).'`';
                $pdo->exec($sql);
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }

        }
        return $this->mysql;
    }

    /**
     * Gets postgresql connection if available.
     *
     * @return T_Postgres_Connection
     */
    function getPostgresConnection()
    {
        if (!is_null($this->pgsql)) return $this->pgsql;
        $data = array();
        $data['host']    = (string) $this->xml->postgresql->host;
        $data['user']    = (string) $this->xml->postgresql->user;
        $data['passwd']  = (string) $this->xml->postgresql->password;
        $data['name']    = (string) $this->xml->postgresql->name;
        $data['port']    = (string) $this->xml->postgresql->port;
        if (strlen($data['port'])==0) $data['port'] = null;

        if (array_sum(array_map('strlen',$data))>0) {
            $this->pgsql = new T_Postgres_Connection($data['host'],
                                           $data['user'],
                                           $data['passwd'],
                                           $data['name'],
                                           $data['port']);
        } else {
            $this->pgsql = false;
        }

        if ($this->pgsql) {
            try {
                $pdo = $this->pgsql->connect();
            } catch (T_Exception_Db $e) {
                $msg = "Connection to test db failed (".
                       $e->getMessage().
                       "). Check parameters in unit.xml config.";
                throw new RuntimeException($msg);
            }
        }
        return $this->pgsql;
    }

    /**
     * Setup the SQL in a particular directory.
     *
     * @return T_Unit_Factory  fluent
     */
    function setupSqlIn($dir,array $dbs)
    {
        $dir = rtrim(rtrim($dir,DIRECTORY_SEPARATOR),'/').'/';
        foreach ($dbs as $type => $db) {
            $sql = file_get_contents($dir.'setup'.$type);
            $db->master()->load($sql);
        }
        return $this;
    }

    /**
     * Teardown the SQL in a particular directory.
     *
     * @return T_Unit_Factory  fluent
     */
    function teardownSqlIn($dir,array $dbs)
    {
        $dir = rtrim(rtrim($dir,DIRECTORY_SEPARATOR),'/').'/';
        foreach ($dbs as $type => $db) {
            $sql = file_get_contents($dir.'teardown'.$type);
            $db->master()->load($sql);
        }
        return $this;
    }

    /**
     * Gets the Google maps API key if available.
     *
     * @return string|false
     */
    function getGoogleMapKey()
    {
        if (isset($this->xml->google->maps)) {
            $key = (string) $this->xml->google->maps;
            if ($key) return $key;
        }
        return false;
    }

    /**
     * Gets the root web URL if available.
     *
     * @return string|false
     */
    function getWebUrl()
    {
        if (isset($this->xml->web->url)) {
            $url = (string) $this->xml->web->url;
            if ($url) return $url;
        }
        return false;
    }

}
