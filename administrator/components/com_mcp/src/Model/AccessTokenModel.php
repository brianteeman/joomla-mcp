<?php

/**
 * @package     Joomla.MCP
 * @subpackage  com_mcp
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Component\MCP\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * MCP Model
 *
 * @since  __DEPLOY_VERSION__
 */
class AccessTokenModel extends BaseDatabaseModel
{
    /**
     * Store an access token in the database
     *
     * @param   array  $data
     *
     * @return void
     * @since __DEPLOY_VERSION__
     */
    public function store(array $data): void
    {
        $db     = $this->getDatabase();
        $time   = time();
        $object = (object) [
            'pid'          => $data['pid'] ?? 0,
            'tstamp'       => $data['tstamp'] ?? $time,
            'crdate'       => $data['crdate'] ?? $time,
            'token'        => $data['token'],
            'userid'       => $data['userid'],
            'client_name'  => $data['client_name'],
            'expires'      => $data['expires'],
            'last_used'    => $data['last_used'],
            'created_ip'   => $data['created_ip'],
            'last_used_ip' => $data['last_used_ip'],
        ];
        if (!$db->insertObject('#__mcp_access_tokens', $object)) {
            throw new \RuntimeException('Failed to insert access token');
        }
    }

    public function getByToken(string $token, ?int $time = null, bool $deleted = false)
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->select('*')
            ->from('#__mcp_access_tokens')
            ->where('token = ' . $db->quote($token))
            ->where('tstamp >= ' . ($time ?? time()))
            ->where('deleted = ' . (int) $deleted);

        return $db->setQuery($query)->loadAssoc();
    }

    public function updateUsage(int $uid, string $ip, ?int $time = null): void
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->update('#__mcp_access_tokens')
            ->set('last_used = ' . ($time ?? time()))
            ->set('last_used_ip = ' . $db->quote($ip))
            ->where('uid = ' . $uid);
        $db->setQuery($query)->execute();
    }

    public function getByUserid(int $userid, ?int $time = null, bool $deleted = false)
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->select('*')
            ->from('#__mcp_access_tokens')
            ->where('userid = ' . $userid)
            ->where('tstamp >= ' . ($time ?? time()))
            ->where('deleted = ' . (int) $deleted);

        return $db->setQuery($query)->loadAssocList();
    }

    public function revoke(int $uid, int $userid): void
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->update('#__mcp_access_tokens')
            ->set('deleted = 1')
            ->set('tstamp = ' . time())
            ->where('uid = ' . $uid)
            ->where('userid = ' . $userid);
        $db->setQuery($query)->execute();
    }

    public function revokeAllForUser(int $userid): void
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->update('#__mcp_access_tokens')
            ->set('deleted = 1')
            ->set('tstamp = ' . time())
            ->where('userid = ' . $userid);
        $db->setQuery($query)->execute();
    }

    public function deleteExpired(?int $time = null): void
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery();
        $query->update('#__mcp_access_tokens')
            ->set('deleted = 1')
            ->set('tstamp = ' . time())
            ->where('expires < ' . ($time ?? time()));
    }
}
