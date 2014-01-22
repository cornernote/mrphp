<?php
require_once(dirname(__FILE__) . '/MrInstance.php');

/**
 * MrEmailReader allows reading of imap emails.
 *
 * @property resource $imap Imap connection
 *
 * Usage
 *
 * // load the users config
 * $config = include('config.php');
 * // init the class
 * $emailReader = MrEmailReader::createInstance($config);
 *
 * Credits
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Zain ul abidin <zainengineer@gmail.com>
 * @copyright Copyright (c) 2014, Brett O'Donnell and Zain ul abidin
 *
 * @license BSD-3-Clause https://raw.github.com/cornernote/mrphp/master/LICENSE
 */
class MrEmailReader extends MrInstance
{

    /**
     * @var string Mail Host
     */
    public $mailHost = '{imap.gmail.com:993/imap/ssl}';

    /**
     * @var string Mail Username
     */
    public $mailUser;

    /**
     * @var string Mail Password
     */
    public $mailPass;

    /**
     * @var string Label to find messages
     */
    public $mailLabel = 'INBOX';

    /**
     * @var resource IMAP connection
     */
    private $_imap;

    /**
     * @var resource IMAP connection
     */
    public function getImap()
    {
        if ($this->_imap)
            return $this->_imap;
        $this->_imap = imap_open($this->mailHost . $this->mailLabel, $this->mailUser, $this->mailPass);
        if (!$this->_imap)
            throw new Exception('Cannot connect: ' . imap_last_error());
        return $this->_imap;
    }

    /**
	 *
     */
    public function close()
    {
        imap_close($this->imap);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getEmails()
    {
        $emails = imap_search($this->imap, 'ALL', SE_UID);
        if ($emails === false)
            throw new Exception('Cannot get emails: ' . imap_last_error());
        return $emails;
    }

    /**
     * @return string
     */
    public function getHeader($email_uid)
    {
        return imap_fetchheader($this->imap, $email_uid, FT_UID);
    }

    /**
     * @return string
     */
    public function getPlainTextBody($email_uid)
    {
        $structure = imap_fetchstructure($this->imap, $email_uid, FT_UID);
        $part = 1.1;
        $message = imap_fetchbody($this->imap, $email_uid, $part, CP_UID);
        if (!$message) {
            $part = 1;
            $message = imap_fetchbody($this->imap, $email_uid, $part, CP_UID);
        }
        return $this->decodeMessage($message, $structure->parts[$part]->encoding);
    }

    /**
	 *
     */
    public function move($email_uid, $label)
    {
        imap_mail_move($this->imap, $email_uid, $label, CP_UID);
    }

    /**
     * @param string $message
     * @param integer $encoding
     * @return string
     */
    private function decodeMessage($message, $encoding)
    {
        if ($encoding == 1)
            return imap_8bit($message);
        if ($encoding == 2)
            return imap_binary($message);
        if ($encoding == 3)
            return imap_base64($message);
        if ($encoding == 4)
            return quoted_printable_decode($message);
        return $message;
    }

}