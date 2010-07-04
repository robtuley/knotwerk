<?php
/**
 * IP address hammer attack lock observer.
 *
 * This observer locks all attempted logins from a particular IP address
 * after a threshold of consecutive failed login attempts is reached.
 *
 * @license http://knotwerk.com/licence MIT
 */
class T_Auth_UserHammer implements T_Auth_Observer
{

    protected $threshold;
    protected $duration;
    protected $db;

    /**
     * Create user account hammer lock protection.
     *
     * @param T_Db $db
     * @param int $threshold  consecutive login failures before lock applied
     * @param int $lock_duration  length of a lockdown in seconds
     */
    function __construct(T_Db $db,$threshold=15,$lock_duration=3600)
    {
        $this->db = $db;
        $this->threshold = $threshold;
        $this->duration = $lock_duration;
    }

    /**
     * Authenticated OK.
     *
     * @param T_Auth  authentication
     * @return T_Auth_Observer  fluent interface
     */
    function pass(T_Auth $auth)
    {
        $user = $auth->getUser();
        if (!$user) return $this; // only action if user available

        // * if account is *already* locked, throw error
        // * if not locked, then reset number of consecutive failed logins
        $db = $this->db->master();
        $db->begin();
        $row = $this->getExistingRow($user);
        if (false!==$row) {
            if (strlen($row['expiry'])) {
                $db->commit();
                throw $this->getError($row['expiry']);
            } else {
                $sql = 'DELETE FROM person_hammer_lock WHERE person=?';
                $db->query($sql,array($user->getId()));
            }
        }
        $db->commit();
        $this->gc();
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
        $user = $auth->getUser();
        if (!$user) return $this; // only action if user available

        $db = $this->db->master();
        $db->begin();
        $row = $this->getExistingRow($user);
        if (false===$row) {
            // insert row
            $sql = 'INSERT INTO person_hammer_lock (person,fail_count) '.
                   'VALUES (?,1)';
            $db->query($sql,array($user->getId()));
        } elseif (strlen($row['expiry'])) {
            // account already locked, throw error
            $db->commit();
            throw $this->getError($row['expiry']);
        } elseif (($this->threshold-$row['fail_count'])<=1) {
            // lock account, reached or gone over threshold
            $expiry = time()+$this->duration;
            $sql = "UPDATE person_hammer_lock SET expiry=? WHERE person=?";
            $db->query($sql,array($expiry,$user->getId()));
            $db->commit();
            throw $this->getError($expiry);
        } else {
            // existing row, under threshold so simply update.
            $sql = 'UPDATE person_hammer_lock SET fail_count=fail_count+1 '.
                   'WHERE person=?';
            $db->query($sql,array($user->getId()));
        }
        $db->commit();
        $this->gc();
        return $this;
    }

    // NB: assumes sitting in a transaction
    protected function getExistingRow($user)
    {
        $db = $this->db->master();

        // retrieve the number of previous consecutive failed logins
        $sql = 'SELECT fail_count,expiry '.
               'FROM person_hammer_lock WHERE person=?';
        if (!$this->db->is(T_Db::SQLITE)) $sql .= ' FOR UPDATE';
         // ^ row-lock if available to prevent race conditions
        $result = $db->query($sql,array($user->getId()));

        // if there is a row, retrieve data
        $row = false;
        if (count($result)>0) $row = $result->fetch();

        // clear row if has expired
        if (false!==$row && strlen($row['expiry']) && $row['expiry']<time()) {
            // expired lock
            $sql = 'DELETE FROM person_hammer_lock '.
                   'WHERE person=?';
            $db->query($sql,array($user->getId()));
            $row=false;
        }

        return $row;
    }

    protected function getError($expiry)
    {
        $f = new T_Filter_HumanTimePeriod;
        $time = $f->transform($expiry-time());
        $msg = 'Due to unusual activity, your account has been temporarily '.
               "disabled for the next $time. ".
               'Please wait and try again later, or contact your system '.
               'administrator.';
        return new T_Exception_Auth($msg);
    }

    /**
     * The general garbage collection mechanism is called separately from the
     * main DB transaction to prevent transaction deadlock (potentially 2 calls
     * to delete an expired row that is already locked for viewing by another
     * thread).
     *
     * This GC clears any old locks for accounts that haven't been retried and
     * deletes them on a 1% probablility basis.
     */
    protected function gc()
    {
        if (mt_rand(1,100)==25) { // 1% chance
            $sql = 'DELETE FROM person_hammer_lock '.
                   'WHERE expiry<?';
            $this->db->master()->query($sql,array(time()));
        }
    }

}
