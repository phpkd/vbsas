<?php
if (!class_exists('vB_DataManager', false))
{
	exit;
}

/**
* Class to do data ban/delete operations for PHPKD_VBSAS
*
* Available info fields:
* $this->info['coppauser'] - User is COPPA
* $this->info['override_usergroupid'] - Prevent overwriting of usergroupid (for email validation)
*
* @package	vBulletin
* @version	$Revision: 62705 $
* @date		$Date: 2012-05-16 15:42:47 -0700 (Wed, 16 May 2012) $
*/
class vB_DataManager_PHPKD_VBSAS extends vB_DataManager
{
	/**
	* Array of recognised and required fields for users, and their types
	*
	* @var	array
	*/
	var $validfields = array(
		'userid'             => array(TYPE_UINT,       REQ_INCR, VF_METHOD, 'verify_nonzero'),
		'username'           => array(TYPE_STR,        REQ_YES,  VF_METHOD),

		'email'              => array(TYPE_STR,        REQ_YES,  VF_METHOD, 'verify_useremail'),
		'parentemail'        => array(TYPE_STR,        REQ_NO,   VF_METHOD),
		'emailstamp'         => array(TYPE_UNIXTIME,   REQ_NO),

		'password'           => array(TYPE_STR,        REQ_YES,  VF_METHOD),
		'passworddate'       => array(TYPE_STR,        REQ_AUTO),
		'salt'               => array(TYPE_STR,        REQ_AUTO, VF_METHOD),

		'usergroupid'        => array(TYPE_UINT,       REQ_YES,  VF_METHOD),
		'membergroupids'     => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_commalist'),
		'infractiongroupids' => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_commalist'),
		'infractiongroupid'  => array(TYPE_UINT,       REQ_NO,),
		'displaygroupid'     => array(TYPE_UINT,       REQ_NO,   VF_METHOD),

		'styleid'            => array(TYPE_UINT,       REQ_NO),
		'languageid'         => array(TYPE_UINT,       REQ_NO),

		'options'            => array(TYPE_UINT,       REQ_YES),
		'adminoptions'       => array(TYPE_UINT,       REQ_NO),
		'showvbcode'         => array(TYPE_INT,        REQ_NO, 'if (!in_array($data, array(0, 1, 2))) { $data = 1; } return true;'),
		'showbirthday'       => array(TYPE_INT,        REQ_NO, 'if (!in_array($data, array(0, 1, 2, 3))) { $data = 2; } return true;'),
		'threadedmode'       => array(TYPE_INT,        REQ_NO,   VF_METHOD),
		'maxposts'           => array(TYPE_INT,        REQ_NO,   VF_METHOD),
		'ipaddress'          => array(TYPE_STR,        REQ_NO,   VF_METHOD),
		'referrerid'         => array(TYPE_NOHTMLCOND, REQ_NO,   VF_METHOD),
		'posts'              => array(TYPE_UINT,       REQ_NO),
		'daysprune'          => array(TYPE_INT,        REQ_NO),
		'startofweek'        => array(TYPE_INT,        REQ_NO),
		'timezoneoffset'     => array(TYPE_STR,        REQ_NO),
		'autosubscribe'      => array(TYPE_INT,        REQ_NO,   VF_METHOD),

		'homepage'           => array(TYPE_NOHTML,     REQ_NO,   VF_METHOD),
		'icq'                => array(TYPE_NOHTML,     REQ_NO),
		'aim'                => array(TYPE_NOHTML,     REQ_NO),
		'yahoo'              => array(TYPE_NOHTML,     REQ_NO),
		'msn'                => array(TYPE_STR,        REQ_NO,   VF_METHOD),
		'skype'              => array(TYPE_NOHTML,     REQ_NO,   VF_METHOD),

		'usertitle'          => array(TYPE_STR,        REQ_NO),
		'customtitle'        => array(TYPE_UINT,       REQ_NO, 'if (!in_array($data, array(0, 1, 2))) { $data = 0; } return true;'),

		'ipoints'            => array(TYPE_UINT,       REQ_NO),
		'infractions'        => array(TYPE_UINT,       REQ_NO),
		'warnings'           => array(TYPE_UINT,       REQ_NO),

		'joindate'           => array(TYPE_UNIXTIME,   REQ_AUTO),
		'lastvisit'          => array(TYPE_UNIXTIME,   REQ_NO),
		'lastactivity'       => array(TYPE_UNIXTIME,   REQ_NO),
		'lastpost'           => array(TYPE_UNIXTIME,   REQ_NO),
		'lastpostid'         => array(TYPE_UINT,       REQ_NO),

		'birthday'           => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD),
		'birthday_search'    => array(TYPE_STR,        REQ_AUTO),

		'reputation'         => array(TYPE_NOHTML,     REQ_NO,   VF_METHOD),
		'reputationlevelid'  => array(TYPE_UINT,       REQ_AUTO),

		'avatarid'           => array(TYPE_UINT,       REQ_NO),
		'avatarrevision'     => array(TYPE_UINT,       REQ_NO),
		'profilepicrevision' => array(TYPE_UINT,       REQ_NO),
		'sigpicrevision'     => array(TYPE_UINT,       REQ_NO),

		'pmpopup'            => array(TYPE_INT,        REQ_NO),
		'pmtotal'            => array(TYPE_UINT,       REQ_NO),
		'pmunread'           => array(TYPE_UINT,       REQ_NO),

		'newrepcount'        => array(TYPE_UINT,       REQ_NO),

		'assetposthash'      => array(TYPE_STR,        REQ_NO),

		// socnet counter fields
		'profilevisits'      => array(TYPE_UINT,       REQ_NO),
		'friendcount'        => array(TYPE_UINT,       REQ_NO),
		'friendreqcount'     => array(TYPE_UINT,       REQ_NO),
		'vmunreadcount'      => array(TYPE_UINT,       REQ_NO),
		'vmmoderatedcount'   => array(TYPE_UINT,       REQ_NO),
		'pcunreadcount'      => array(TYPE_UINT,       REQ_NO),
		'pcmoderatedcount'   => array(TYPE_UINT,       REQ_NO),
		'gmmoderatedcount'   => array(TYPE_UINT,       REQ_NO),

		// usertextfield fields
		'subfolders'         => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_serialized'),
		'pmfolders'          => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_serialized'),
		'searchprefs'        => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_serialized'),
		'buddylist'          => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_spacelist'),
		'ignorelist'         => array(TYPE_NOCLEAN,    REQ_NO,   VF_METHOD, 'verify_spacelist'),
		'signature'          => array(TYPE_STR,        REQ_NO),
		'rank'               => array(TYPE_STR,        REQ_NO),

		// facebook fields
		'fbuserid'           => array(TYPE_STR,        REQ_NO),
		'fbname'             => array(TYPE_STR,        REQ_NO),
		'fbjoindate'         => array(TYPE_UINT,       REQ_NO),
		'logintype'          => array(TYPE_STR,        REQ_NO, 'if (!in_array($data, array(\'vb\', \'fb\'))) { $data = \'vb\'; } return true; ')
	);

	/**
	* Array of field names that are bitfields, together with the name of the variable in the registry with the definitions.
	*
	* @var	array
	*/
	var $bitfields = array(
		'options'      => 'bf_misc_useroptions',
		'adminoptions' => 'bf_misc_adminoptions',
	);

	/**
	* The main table this class deals with
	*
	* @var	string
	*/
	var $table = 'user';

	/**#@+
	* Arrays to store stuff to save to user-related tables
	*
	* @var	array
	*/
	var $user = array();
	var $userfield = array();
	var $usertextfield = array();
	/**#@-*/

	/**
	* Condition for update query
	*
	* @var	array
	*/
	var $condition_construct = array('userid = %1$d', 'userid');

	/**
	* Constructor - checks that the registry object has been passed correctly.
	*
	* @param	vB_Registry	Instance of the vBulletin data registry object - expected to have the database object as one of its $this->db member.
	* @param	integer		One of the ERRTYPE_x constants
	*/
	function vB_DataManager_PHPKD_VBSAS(&$registry, $errtype = ERRTYPE_SILENT)
	{
		parent::vB_DataManager($registry, $errtype);
	}

	/**
	* Deletes a user
	*
	* @return	mixed	The number of affected rows
	*/
	function delete($doquery = true)
	{
		if (!$this->existing['userid'])
		{
			return false;
		}

		// make sure we are not going to delete the last admin o.O
		if ($this->is_admin($this->existing['usergroupid'], $this->existing['membergroupids']) AND $this->count_other_admins($this->existing['userid']) == 0)
		{
			$this->error('cant_delete_last_admin');
			return false;
		}

		if (!$this->pre_delete($doquery))
		{
			return false;
		}


		// Delete Social Data (Socialgroups, Picture Albums, Picture Comments, Visitor Messages and related memberships & subscriptions)
		if ($this->registry->GPC['del_social'] || $this->registry->GPC['del_account'] == 1)
		{
			// Discussions (No DM since there is NO userid in the 'discussion' table)
			$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "discussion SET lastposter = '" . $this->registry->db->escape_string($this->existing['username']) . "', lastposterid = 0 WHERE lastposterid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "discussionread WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribediscussion WHERE userid = " . $this->existing['userid']);

			// Visitor Messages
			require_once(DIR . '/includes/functions_visitormessage.php');
			$visitormessagesql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "visitormessage AS visitormessage WHERE visitormessage.userid = " . $this->existing['userid']);
			while ($visitormessageinfo = $this->registry->db->fetch_array($visitormessagesql))
			{
				$visitormessagedm =& datamanager_init('VisitorMessage', $this->registry, ERRTYPE_SILENT);
				$visitormessagedm->set_existing($visitormessageinfo);
				$visitormessagedm->set_info('hard_delete', !$this->registry->GPC['softdeletion']);
				$visitormessagedm->set_info('reason', $this->registry->GPC['deletereason']);
				$visitormessagedm->delete();
				unset($visitormessagedm);
			}

			// Socialgroup Subscriptions
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "groupread WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribegroup WHERE userid = " . $this->existing['userid']);

			// Socialgoups
			$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "socialgroup SET transferowner = 0 WHERE transferowner = " . $this->existing['userid']);
			$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "socialgroup SET lastposter = '" . $this->registry->db->escape_string($phpkd_vbsas['username']) . "', lastposterid = 0 WHERE lastposterid = " . $this->existing['userid']);

			$groups = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "socialgroup WHERE creatoruserid = " . $this->existing['userid']);

			$groupsowned = array();
			while ($group = $this->registry->db->fetch_array($groups))
			{
				$groupsowned[] = $group['groupid'];
			}
			$this->registry->db->free_result($groups);

			if (!empty($groupsowned))
			{
				require_once(DIR . '/includes/functions_socialgroup.php');

				foreach($groupsowned AS $groupowned)
				{
					$group = fetch_socialgroupinfo($groupowned);
					if (!empty($group))
					{
						// dm will have problem if the group is invalid, and in all honesty, at this situation,
						// if the group is no longer present, then we don't need to worry about it anymore.
						$socialgroupdm = datamanager_init('SocialGroup', $this->registry, ERRTYPE_SILENT);
						$socialgroupdm->set_existing($group);
						$socialgroupdm->delete();
					}
				}
			}

			// Socialgroup Memberships
			$groupmemberships = $this->registry->db->query_read("
				SELECT socialgroup.*
				FROM " . TABLE_PREFIX . "socialgroupmember AS socialgroupmember
				INNER JOIN " . TABLE_PREFIX . "socialgroup AS socialgroup ON (socialgroup.groupid = socialgroupmember.groupid)
				WHERE socialgroupmember.userid = " . $this->existing['userid']
			);

			$socialgroups = array();
			while ($groupmembership = $this->registry->db->fetch_array($groupmemberships))
			{
				$socialgroups["$groupmembership[groupid]"] = $groupmembership;
			}

			if (!empty($socialgroups))
			{
				$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "socialgroupmember WHERE userid = "  . $this->existing['userid']);

				foreach ($socialgroups AS $group)
				{
					$groupdm =& datamanager_init('SocialGroup', $this->registry, ERRTYPE_SILENT);
					$groupdm->set_existing($group);
					$groupdm->rebuild_membercounts();
					$groupdm->rebuild_picturecount();
					$groupdm->save();

					list($pendingcountforowner) = $this->registry->db->query_first("SELECT SUM(moderatedmembers) FROM " . TABLE_PREFIX . "socialgroup WHERE creatoruserid = " . $group['creatoruserid'], DBARRAY_NUM);
					$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "user SET socgroupreqcount = " . intval($pendingcountforowner) . " WHERE userid = " . $group['creatoruserid']);
				}

				unset($groupdm);
			}

			// Socialgroup Pictures
			$types = vB_Types::instance();
			$picture_sql = $this->registry->db->query_read("
				SELECT a.attachmentid, a.filedataid, a.userid
				FROM " . TABLE_PREFIX . "attachment AS a
				WHERE a.userid = " . $this->existing['userid'] . " AND a.contenttypeid IN (" . intval($types->getContentTypeID('vBForum_SocialGroup')) . "," . intval($types->getContentTypeID('vBForum_Album')) . ")
			");

			$attachdm =& datamanager_init('Attachment', $this->registry, ERRTYPE_SILENT, 'attachment');
			while ($picture = $this->registry->db->fetch_array($picture_sql))
			{
				$attachdm->set_existing($picture);
				$attachdm->delete();
			}

			// Albums
			$albumsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "album AS album WHERE album.userid = " . $this->existing['userid']);
			while ($albuminfo = $this->registry->db->fetch_array($albumsql))
			{
				$albumdata =& datamanager_init('Album', $this->registry, ERRTYPE_SILENT);
				$albumdata->set_existing($albuminfo);
				$albumdata->delete();
				unset($albumdata);
			}

			// PictureComment
			$picturecommentsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "picturecomment AS picturecomment WHERE picturecomment.userid = " . $this->existing['userid']);
			while ($picturecommentinfo = $this->registry->db->fetch_array($picturecommentsql))
			{
				$picturecommentdata =& datamanager_init('PictureComment', $this->registry, ERRTYPE_SILENT);
				$picturecommentdata->set_existing($picturecommentinfo);
				$picturecommentdata->set_info('hard_delete', !$this->registry->GPC['softdeletion']);
				$picturecommentdata->set_info('reason', $this->registry->GPC['deletereason']);
				$picturecommentdata->delete();
				unset($picturecommentdata);
			}

			if ($this->registry->GPC['del_social'])
			{
				// Visitor Messages
				require_once(DIR . '/includes/functions_visitormessage.php');
				$visitormessagesql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "visitormessage AS visitormessage WHERE visitormessage.postuserid = " . $this->existing['userid']);
				while ($visitormessageinfo = $this->registry->db->fetch_array($visitormessagesql))
				{
					$visitormessagedm =& datamanager_init('VisitorMessage', $this->registry, ERRTYPE_SILENT);
					$visitormessagedm->set_existing($visitormessageinfo);
					$visitormessagedm->set_info('hard_delete', !$this->registry->GPC['softdeletion']);
					$visitormessagedm->set_info('reason', $this->registry->GPC['deletereason']);
					$visitormessagedm->delete();
					unset($visitormessagedm);
				}

				// Socialgoup Messages
				$groupmessagesql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "groupmessage AS groupmessage WHERE groupmessage.postuserid = " . $this->existing['userid']);
				while ($groupmessageinfo = $this->registry->db->fetch_array($groupmessagesql))
				{
					$groupmessagedm =& datamanager_init('GroupMessage', $this->registry, ERRTYPE_SILENT);
					$groupmessagedm->set_existing($groupmessageinfo);
					$groupmessagedm->set_info('hard_delete', !$this->registry->GPC['softdeletion']);
					$groupmessagedm->set_info('reason', $this->registry->GPC['deletereason']);
					$groupmessagedm->delete();
					unset($groupmessagedm);
				}

				// PictureComment
				$picturecommentsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "picturecomment AS picturecomment WHERE picturecomment.postuserid = " . $this->existing['userid']);
				while ($picturecommentinfo = $this->registry->db->fetch_array($picturecommentsql))
				{
					$picturecommentdata =& datamanager_init('PictureComment', $this->registry, ERRTYPE_SILENT);
					$picturecommentdata->set_existing($picturecommentinfo);
					$picturecommentdata->set_info('hard_delete', !$this->registry->GPC['softdeletion']);
					$picturecommentdata->set_info('reason', $this->registry->GPC['deletereason']);
					$picturecommentdata->delete();
					unset($picturecommentdata);
				}
			}

			if ($this->registry->GPC['del_account'] == 1)
			{
				// Visitor Messages
				$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "visitormessage SET postuserid = 0 WHERE postuserid = " . $this->existing['userid']);

				// Socialgoup Messages
				$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "groupmessage SET postuserid = 0 WHERE postuserid = " . $this->existing['userid']);

				// Socialgoup Messages
				$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "picturecomment SET postuserid = 0 WHERE postuserid = " . $this->existing['userid']);
			}
		}


		// Delete Calendar Data
		if ($this->registry->GPC['del_calendar'] || $this->registry->GPC['del_account'] == 1)
		{
			$eventsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "event AS event WHERE event.userid = " . $this->existing['userid']);
			while ($eventinfo = $this->registry->db->fetch_array($eventsql))
			{
				$eventdata =& datamanager_init('Event', $this->registry, ERRTYPE_SILENT);
				$eventdata->set_existing($eventinfo);
				$eventdata->delete();
				unset($eventdata);
			}

			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "reminder WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "calendarmoderator WHERE userid = " . $this->existing['userid']);
		}


		// Delete Threads, Posts, Polls and related subscriptions
		if ($this->registry->GPC['del_threadpost'] || $this->registry->GPC['del_account'] == 1)
		{
			// Subscriptions
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribeforum WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscribethread WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "subscriptionlog WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "threadread WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "forumread WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "threadrate WHERE userid = " . $this->existing['userid']);

			// PollVote
			$pollvotesql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "pollvote AS pollvote WHERE pollvote.userid = " . $this->existing['userid']);
			while ($pollvoteinfo = $this->registry->db->fetch_array($pollvotesql))
			{
				$pollvotedata =& datamanager_init('PollVote', $this->registry, ERRTYPE_SILENT);
				$pollvotedata->set_existing($pollvoteinfo);
				$pollvotedata->delete();
				unset($pollvotedata);
			}

			// Common data
			$threadarr = array();
			$forumarr = array();

			if ($this->registry->GPC['del_threadpost'])
			{
				// Announcements
				$announcementsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "announcement AS announcement WHERE announcement.userid = " . $this->existing['userid']);
				while ($announcementinfo = $this->registry->db->fetch_array($announcementsql))
				{
					$anncdata =& datamanager_init('Announcement', $this->registry, ERRTYPE_SILENT);
					$anncdata->set_existing($announcementinfo);
					$anncdata->delete();
					unset($anncdata);
				}

				// Posts
				$postsql = $this->registry->db->query_read("SELECT post.*, thread.forumid FROM " . TABLE_PREFIX . "post AS post LEFT JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid) WHERE post.userid = " . $this->existing['userid']);
				while ($postinfo = $this->registry->db->fetch_array($postsql))
				{
					$postdata =& datamanager_init('Post', $this->registry, ERRTYPE_SILENT, 'threadpost');
					$postdata->set_existing($postinfo);
					$postdata->delete(true, $postinfo['threadid'], !$this->registry->GPC['softdeletion'], array('userid' => $this->registry->userinfo['userid'], 'username' => $this->registry->userinfo['username'], 'reason' => $this->registry->GPC['deletereason']));
					unset($postdata);

					$threadarr[] = $postinfo['threadid'];
					$forumarr[] = $postinfo['forumid'];
				}

				// Threads
				$threadsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "thread AS thread WHERE thread.postuserid = " . $this->existing['userid']);
				while ($threadinfo = $this->registry->db->fetch_array($threadsql))
				{
					$threaddata =& datamanager_init('Thread', $this->registry, ERRTYPE_SILENT, 'threadpost');
					$threaddata->set_existing($threadinfo);
					$threaddata->delete(true, !$this->registry->GPC['softdeletion'], array('userid' => $this->registry->userinfo['userid'], 'username' => $this->registry->userinfo['username'], 'reason' => $this->registry->GPC['deletereason']));
					unset($threaddata);

					$forumarr[] = $threadinfo['forumid'];
				}
			}

			if ($this->registry->GPC['del_account'] == 1)
			{
				// Announcements
				$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "announcement SET userid = 0 WHERE userid = " . $this->existing['userid']);

				// Posts
				$postsql = $this->registry->db->query_read("SELECT post.*, thread.forumid FROM " . TABLE_PREFIX . "post AS post LEFT JOIN " . TABLE_PREFIX . "thread AS thread USING (threadid) WHERE post.userid = " . $this->existing['userid']);
				while ($postinfo = $this->registry->db->fetch_array($postsql))
				{
					$threadarr[] = $postinfo['threadid'];
					$forumarr[] = $postinfo['forumid'];
				}
				$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "post SET userid = 0 WHERE userid = " . $this->existing['userid']);

				// Threads
				$threadsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "thread AS thread WHERE thread.postuserid = " . $this->existing['userid']);
				while ($threadinfo = $this->registry->db->fetch_array($threadsql))
				{
					$forumarr[] = $threadinfo['forumid'];
				}
				$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "thread SET postuserid = 0 WHERE postuserid = " . $this->existing['userid']);
			}

			if (!empty($threadarr))
			{
				foreach ($threadarr as $threadid)
				{
					build_thread_counters($threadid);
				}
			}

			if (!empty($forumarr))
			{
				foreach ($forumarr as $forumid)
				{
					build_forum_counters($forumid);
				}
			}
		}



		// Delete User Profile Data
		if ($this->registry->GPC['del_account'] == 1)
		{
			// Traces
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "access WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "session WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "cpsession WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "infraction WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "reputation WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "profileblockprivacy WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "activitystream WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "autosave WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "customprofile WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "ipdata WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "adminlog WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "moderator WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "moderatorlog WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "deletionlog WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "noticedismissed WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "passwordhistory WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "usergrouprequest WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "pmthrottle WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "profilevisitor WHERE userid = " . $this->existing['userid'] . " OR visitorid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "searchcore WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "searchgroup WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "searchlog WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "sigparsed WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "skimlinks WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachyforumcounter WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachyforumpost WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachythreadcounter WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachythreadpost WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "tachythreadpost WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "useractivation WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "userban WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "userchangelog WHERE userid = " . $this->existing['userid'] . " OR adminid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "usercss WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "usercsscache WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "usergroupleader WHERE userid = " . $this->existing['userid']);
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "usernote WHERE userid = " . $this->existing['userid'] . " OR posterid = " . $this->existing['userid']);


			// Custom Profile Pictures
			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "customavatar WHERE userid = " . $this->existing['userid']);
			@unlink($this->registry->options['avatarpath'] . '/avatar' . $this->existing['userid'] . '_' . $this->existing['avatarrevision'] . '.gif');

			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "customprofilepic WHERE userid = " . $this->existing['userid']);
			@unlink($this->registry->options['profilepicpath'] . '/profilepic' . $this->existing['userid'] . '_' . $this->existing['profilepicrevision'] . '.gif');

			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "sigpic WHERE userid = " . $this->existing['userid']);
			@unlink($this->registry->options['sigpicpath'] . '/sigpic' . $this->existing['userid'] . '_' . $this->existing['sigpicrevision'] . '.gif');


			// Admin Permissions
			$admindm =& datamanager_init('Admin', $this->registry, ERRTYPE_SILENT);
			$admindm->set_existing($this->existing);
			$admindm->delete();
			unset($admindm);


			// Delete Infraction
			$infractionsql = $this->registry->db->query_read("SELECT * FROM " . TABLE_PREFIX . "infraction AS infraction WHERE infraction.userid = " . $this->existing['userid']);
			while ($infractioninfo = $this->registry->db->fetch_array($infractionsql))
			{
				$infractiondata =& datamanager_init('Infraction', $this->registry, ERRTYPE_SILENT);
				$infractiondata->set_existing($infractioninfo);
				$infractiondata->delete();
				unset($infractiondata);
			}


			// Friendship Relations
			$pendingfriends = array();
			$currentfriends = array();

			$friendlist = $this->registry->db->query_read("
				SELECT relationid, friend
				FROM " . TABLE_PREFIX . "userlist
				WHERE userid = " . $this->existing['userid'] . "
					AND type = 'buddy'
					AND friend IN('pending','yes')
			");

			while ($friend = $this->registry->db->fetch_array($friendlist))
			{
				if ($friend['friend'] == 'yes')
				{
					$currentfriends[] = $friend['relationid'];
				}
				else
				{
					$pendingfriends[] = $friend['relationid'];
				}
			}

			if (!empty($pendingfriends))
			{
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET friendreqcount = IF(friendreqcount > 0, friendreqcount - 1, 0)
					WHERE userid IN (" . implode(", ", $pendingfriends) . ")
				");
			}

			if (!empty($currentfriends))
			{
				$this->registry->db->query_write("
					UPDATE " . TABLE_PREFIX . "user
					SET friendcount = IF(friendcount > 0, friendcount - 1, 0)
					WHERE userid IN (" . implode(", ", $currentfriends) . ")
				");
			}

			$this->registry->db->query_write("DELETE FROM " . TABLE_PREFIX . "userlist WHERE userid = " . $this->existing['userid'] . " OR relationid = " . $this->existing['userid']);


			// Private Messages
			require_once(DIR . '/includes/adminfunctions.php');
			delete_user_pms($this->existing['userid'], false);


			// User Profile
			if ($this->db_delete(TABLE_PREFIX, 'user', $this->condition, $doquery))
			{
				$this->db_delete(TABLE_PREFIX, 'userfield', $this->condition, $doquery);
				$this->db_delete(TABLE_PREFIX, 'usertextfield', $this->condition, $doquery);
			}
		}
		else if ($this->registry->GPC['del_account'] == 2)
		{
			$this->registry->db->query_write("
				INSERT INTO " . TABLE_PREFIX . "userban (userid, usergroupid, displaygroupid, customtitle, usertitle, adminid, bandate, liftdate, reason)
				VALUES (" . $this->existing['userid'] . ", " . $this->registry->options['phpkd_vbsas_banugid'] . ", 0, " . ($this->registry->options['phpkd_vbsas_bantitle'] ? 1 : 0) . ", '" . ($this->registry->options['phpkd_vbsas_bantitle'] ? $this->registry->db->escape_string($this->registry->options['phpkd_vbsas_bantitle']) : $this->registry->db->escape_string($this->registry->usergroupcache[$this->registry->options['phpkd_vbsas_banugid']]['usertitle'])) . "', " . $this->registry->userinfo['userid'] . ", " . TIMENOW . ", 0, '" . $this->registry->db->escape_string($this->registry->GPC['deletereason']) . "')
			");

			// update the user record
			$userdm =& datamanager_init('User', $this->registry, ERRTYPE_SILENT);
			$userdm->set_existing($this->existing);
			$userdm->set('usergroupid', $this->registry->options['phpkd_vbsas_banugid']);
			$userdm->set('displaygroupid', 0);

			// update the user's title if they've specified a special user title for the banned group
			$userdm->set('usertitle', $this->registry->options['phpkd_vbsas_bantitle'] ? $this->registry->db->escape_string($this->registry->options['phpkd_vbsas_bantitle']) : $this->registry->usergroupcache[$this->registry->options['phpkd_vbsas_banugid']]['usertitle']);
			$userdm->set('customtitle', $this->registry->options['phpkd_vbsas_bantitle'] ? 1 : 0);

			$userdm->save();
			unset($userdm);
		}

		// Ban IP Address
		if ($this->existing['ipaddress'] && $this->registry->options['phpkd_vbsas_banoptions'] & $this->registry->bf_misc_phpkd_vbsas_banoptions['phpkd_vbsas_ip'])
		{
			$this->registry->db->query_write("UPDATE " . TABLE_PREFIX . "setting SET value = CONCAT(value, ' ', '" . $this->existing['ipaddress'] . "') WHERE varname = 'banip'");
			build_options();
		}

		// Ban Email Address
		if ($this->existing['email'] && $this->registry->options['phpkd_vbsas_banoptions'] & $this->registry->bf_misc_phpkd_vbsas_banoptions['phpkd_vbsas_email'])
		{
			$this->registry->datastore->fetch(array('banemail'));
			build_datastore('banemail', $this->registry->banemail . ' ' . $this->existing['email']);
			build_options();
		}

		if ($this->registry->GPC['del_thirdparty'])
		{
			// Execute any other third-party deletion processes
			($hook = vBulletinHook::fetch_hook('userdata_delete')) ? eval($hook) : false;
		}


		// Re-calculate user stats
		require_once(DIR . '/includes/functions_databuild.php');
		build_user_statistics();
		build_birthdays();
	}

	/**
	* Checks usergroupid and membergroupids to see if the user has admin privileges
	*
	* @param	integer	Usergroupid
	* @param	string	Membergroupids (comma separated)
	*
	* @return	boolean	Returns true if user has admin privileges
	*/
	function is_admin($usergroupid, $membergroupids)
	{
		if ($this->registry->usergroupcache["$usergroupid"]['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['cancontrolpanel'])
		{
			return true;
		}
		else if ($this->registry->usergroupcache["$usergroupid"]['genericoptions'] & $this->registry->bf_ugp_genericoptions['allowmembergroups'])
		{
			if ($membergroupids != '')
			{
				foreach (explode(',', $membergroupids) AS $membergroupid)
				{
					if ($this->registry->usergroupcache["$membergroupid"]['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['cancontrolpanel'])
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	* Checks usergroupid and membergroupids to see if the user has super moderator privileges
	*
	* @param	integer	Usergroupid
	* @param	string	Membergroupids (comma separated)
	*
	* @return	boolean	Returns true if user has super moderator privileges
	*/
	function is_supermod($usergroupid, $membergroupids)
	{
		if ($this->registry->usergroupcache["$usergroupid"]['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['ismoderator'])
		{
			return true;
		}
		else if ($this->registry->usergroupcache["$usergroupid"]['genericoptions'] & $this->registry->bf_ugp_genericoptions['allowmembergroups'])
		{
			if ($membergroupids != '')
			{
				foreach (explode(',', $membergroupids) AS $membergroupid)
				{
					if ($this->registry->usergroupcache["$membergroupid"]['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['ismoderator'])
					{
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	* Counts the number of administrators OTHER THAN the user specified
	*
	* @param	integer	User ID of user to be checked
	*
	* @return	integer	The number of administrators excluding the current user
	*/
	function count_other_admins($userid)
	{
		$admingroups = array();
		$groupsql = '';
		foreach ($this->registry->usergroupcache AS $usergroupid => $usergroup)
		{
			if ($usergroup['adminpermissions'] & $this->registry->bf_ugp_adminpermissions['cancontrolpanel'])
			{
				$admingroups[] = $usergroupid;
				if ($usergroup['genericoptions'] & $this->registry->bf_ugp_genericoptions['allowmembergroups'])
				{
					$groupsql .= "
					OR FIND_IN_SET('$usergroupid', membergroupids)";
				}
			}
		}

		$countadmin = $this->registry->db->query_first("
			SELECT COUNT(*) AS users
			FROM " . TABLE_PREFIX . "user
			WHERE userid <> " . intval($userid) . "
			AND
			(
				usergroupid IN(" . implode(',', $admingroups) . ")" .
				$groupsql . "
			)
		");

		return $countadmin['users'];
	}
}
?>