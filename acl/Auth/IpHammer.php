<?php
/**
 * Defines the T_Auth_IpHammer class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * IP address hammer attack lock observer.
 *
 * This observer locks all attempted logins from a particular IP address after a threshold of
 * of consecutive failed login attempts is reached.
 *
 * @package ACL
 */
class T_Auth_IpHammer implements T_Auth_Observer
{

    /**
     * Consecutive failures permitted before lock.
     *
     * @var int
     */
    protected $threshold;

    /**
     * Lock duration (seconds).
     *
     * @var int
     */
    protected $duration;

    /**
     * Create IP hammer lock protection.
     *
     * @param int $threshold  consecutive login failures before lock applied
     * @param int $lock_duration  length of a lockdown in seconds
     */
    function __construct($threshold=15,$lock_duration=3600)
    {
        $this->threshold = $threshold;
        $this->duration = $lock_duration;
    }

	/**
     * Get connection object
     *
     * @return T_Mysql_Conn
     */
    protected function getConnection()
    {
        return T_Mysql_Factory::getInstance('user');
    }

    /**
     * Get IP address.
     *
     * @return string  IP
     */
    protected function getIp()
    {
        if (_as('SERVER')->exists('REMOTE_ADDR')) {
            return _as('SERVER')->asScalar('REMOTE_ADDR')->uncage();
        } else {
            return false;
        }
    }

    /**
     * Get the expiry of any IP lock.
     *
     * @param string $ip  IP address
     * @param false|int  false or unix time of lock expiry
     */
    protected function getLockExpiry($ip)
    {
        $db = $this->getConnection()->master();
        $ip_sql = 'INET_ATON('.$db->to_sql_literal($ip).')';

        // retrieve the number of previous consecutive failed logins
        $sql = "SELECT fail_count,UNIX_TIMESTAMP(expiry) unix_expiry ".
               "FROM person_hammer_lock WHERE ip=$ip_sql";
        $result = $db->query($sql);

        // if there is a row, retrieve data
        $row = false;
        if ($result->num_rows==1) $row = $result->fetch_object();

        // clear any expired locks
        $sql = 'DELETE FROM person_hammer_lock WHERE expiry < NOW()';
        if ($row && strlen($row->unix_expiry) && $row->unix_expiry<time()) { // expired lock
            $db->query($sql);
            $row=false;
        } elseif (mt_rand(1,50)==25) { // periodically cleanse on 2% probability
            $db->query($sql);
        }

        // work out if lock is already set, and return if it is
        if ($row && strlen($row->unix_expiry)) {
            return $row->unix_expiry;
        }

        // if no lock already, but row is present, check against threshold to see if need to
        // apply a lock...
        if ($row && $row->fail_count>$this->threshold) {
            $expiry = time()+$this->duration;
            $sql = "UPDATE person_hammer_lock SET expiry=FROM_UNIXTIME($expiry) WHERE ip=$ip_sql";
            $db->query($sql);
            return $expiry;
        }

        // ... else no row, or a row which is not already locked and threshold is under limit.
        return false;
    }

    /**
     * Creates the lock error.
     *
     * @param int $expiry
     * @throws T_Exception_Auth
     */
    protected function throwError($expiry)
    {
        $f = new T_Filter_HumanTimePeriod();
        $time = $f->transform($expiry-time());
        $msg = "Due to unusual activity, your account has been temporarily disabled for the next $time. ".
               'Please wait and try again later, or contact your system administrator.';
        throw new T_Exception_Auth($msg);
    }

    /**
     * Authenticated OK.
     *
     * @param T_Auth  authentication
     * @return T_Auth_Observer  fluent interface
     */
    function pass(T_Auth $auth)
    {
        $ip = $this->getIp();
        if (!$ip) {
            return $this; // only action if IP available
        }
        // Check if account is *already* locked, and if so, throw an error. If not
        // already locked, then the login can continue and the number of consecutive
        // failed logins by this IP should be reset.
        $expiry = $this->getLockExpiry($ip);
        if ($expiry!==false) {
            $this->throwError($expiry);
        } else {
            $db = $this->getConnection()->master();
            $ip_sql = 'INET_ATON('.$db->to_sql_literal($ip).')';
            $sql = 'DELETE FROM person_hammer_lock WHERE ip='.$ip_sql;
            $db->query($sql);
        }
        return $this;
    }

    /**
     * Authentication failure.
     *
     * @param T_Auth  authentication
     * @return T_Auth_Observer  fluent interface
     */
    function fail(T_Auth $auth)
    {
        $ip = $this->getIp();
        if (!$ip) {
            return $this; // only action if IP available
        }
        // Check if account is *already* locked, and if so, throw an error. If not
        // already locked, then the failed login should be noted.
        $expiry = $this->getLockExpiry($ip);
        if ($expiry!==false) {
            $this->throwError($expiry);
        } else {
            $db = $this->getConnection()->master();
            $ip_sql = 'INET_ATON('.$db->to_sql_literal($ip).')';
            $sql = "INSERT INTO person_hammer_lock (ip,fail_count,expiry) VALUES ($ip_sql,1,NULL) ".
                   "ON DUPLICATE KEY UPDATE fail_count=fail_count+1";
            $db->query($sql);
        }
    }

}
