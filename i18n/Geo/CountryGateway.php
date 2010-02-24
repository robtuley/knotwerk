<?php
/**
 * Defines the T_Geo_CountryGateway class.
 *
 * @package geo
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Country gateway.
 *
 * @package geo
 */
class T_Geo_CountryGateway
{

    /**
     * Database connection.
     *
     * @var T_Db
     */
    protected $db;

    /**
     * Factory.
     *
     * @var T_Factory
     */
    protected $factory;

    /**
     * Create gateway.
     *
     * @param T_Db $db
     * @param T_Factory $factory
     */
    function __construct(T_Db $db,T_Factory $factory)
    {
        $this->db = $db;
        $this->factory = $factory;
    }

    /**
     * Creates a country object from a table row.
     *
     * @param array $row
     * @return T_Geo_Country
     */
    protected function fromRow($row)
    {
        $row['id'] = (int) $row['id'];
        return $this->factory->like('T_Geo_Country',$row);
    }

    /**
     * Gets a country from a field value.
     *
     * @param string $field  fieldname
     * @param mixed $value  value
     * @return T_Geo_Country
     */
    protected function getBy($field,$value)
    {
        $sql  = 'SELECT id,code,name,url FROM country '.
                "WHERE $field = ?";
        $result = $this->db->slave()->query($sql,array($value));
        if (count($result) === 1) {
            return $this->fromRow($result->fetch());
        } else {
            $msg = "Country with $field=$value not found";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Gets a country from its string code.
     *
     * @param string $code  two character country code
     * @return T_Geo_Country
     */
    function getByCode($code)
    {
        return $this->getBy('code',strtoupper($code));
    }

    /**
     * Gets a country from its ID.
     *
     * @param int $id  ID
     * @return T_Geo_Country
     */
    function getById($id)
    {
        return $this->getBy('id',$id);
    }

    /**
     * Gets a country from its URL.
     *
     * @param string $url  url string
     * @return T_Geo_Country
     */
    function getByUrl($url)
    {
        return $this->getBy('url',strtolower($url));
    }

    /**
     * Gets all the countries.
     *
     * @return T_Geo_Country[]
     */
    function getAll()
    {
        $sql  = 'SELECT id,code,name,url FROM country '.
                'ORDER BY name ASC';
        $result = $this->db->slave()->query($sql);
        $world = array();
        foreach ($result as $row) {
            $world[$row['id']] = $this->fromRow($row);
        }
        return $world;
    }

}
