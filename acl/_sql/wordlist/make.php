<?php
/**
 * This file generates the SQL file required to populate the word-list table from various
 * written word lists.
 *
 * @see http://www.phreak.com/html/wordlists.shtml
 * @see ftp://ftp.funet.fi/pub/unix/security/dictionaries/
 * @see ftp://ftp.ox.ac.uk/pub/wordlists/
 * @see ftp://ftp.cerias.purdue.edu/pub/dict/
 */

/**
 * Config Parameters:
 */
define('WORD_BATCH_SIZE',5000);  // amounts of words added per query
define('FILE_BATCH_SIZE',false); // amounts of words per file, false if all in one file
define('MIN_WORD_LENGTH',4);     // minimum word length to include

/**
 * Load library.
 */
require_once dirname(__FILE__).'/../../../core/_autoload.php';
T_ErrorHandler::setDebug();

/**
 * Work out which files to load.
 */
$here = new T_File_Dir(dirname(__FILE__));
$files = array();
foreach ($here as $dir) {
    if ($dir instanceof T_File_Dir) {
        foreach ($dir as $f) {
            if ($f instanceof T_File_Path) $files[] = $f;
        }
    }
}

/**
 * Load files and convert to SQL.
 */
$ftotal = 0;
$fbatch = FILE_BATCH_SIZE===false ? '' : 0;
$sql = new T_File_Unlocked(new T_File_Path(T_ROOT_DIR.'acl/_sql','wordlist'.$fbatch,'sql'),'wb');
foreach ($files as $f) {
    $words = array();
    $fp = fopen($f->__toString(),'rb');
    while (!feof($fp)) {
        $w = trim(fgets($fp,4096));
        if (mb_strlen($w)>=MIN_WORD_LENGTH && ctype_alnum($w)) $words[] = "'$w'";
        if (count($words)>WORD_BATCH_SIZE) {
            $sql->write('REPLACE INTO pwd_dictionary (word) VALUES ('.implode('),(',$words).');'.EOL);
            $ftotal += count($words);
            $words = array();
            // possibly need a new file at this point..
            if (FILE_BATCH_SIZE!==false && $ftotal>FILE_BATCH_SIZE) {
                $ftotal = 0;
                ++$fbatch;
                $sql->close();
                $sql = new T_File_Unlocked(new T_File_Path(T_ROOT_DIR.'acl/_sql','wordlist'.$fbatch,'sql'),'wb');
            }
        }
    }
    if (count($words)) {
        $sql->write('REPLACE INTO pwd_dictionary (word) VALUES ('.implode('),(',$words).');'.EOL);
    }
}
$sql->close();
