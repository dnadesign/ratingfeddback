<?php

namespace DNADesign\RatingFeedback\Models;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Security\Permission;
use DNADesign\RatingFeedback\Models\RatingFeedback;


class RatingFeedback extends DataObject {

	private static $db = [
		'Rating' => 'Int',
		'Comments' => 'Text'
	];

	private static $has_one = [
		'Page' => SiteTree::class,
		'SubmittedBy' => Member::class
	];

	private static $summary_fields = [
		'ID' => 'ID',
		'Created' => 'Created',
		'Page.Title' => 'Title',
		'Rating' => 'Rating',		
		'Comments' => 'Comments'	
	];

	private static $default_sort = 'Created DESC';

	private static $table_name = 'RatingFeedback';

	/**
	* Permissions
	*/
	public function canCreate($member = NULL, $context = array()) 
	{
		return false;
	}

	public function canView($member = null) 
	{
		return Permission::check('CMS_ACCESS', 'any', $member);
	}

	public function canEdit($member = null) 
	{
	  return false;
	}

	public function canDelete($member = null) 
	{
		return Permission::check('CMS_ACCESS', 'any', $member);
	}

	/**
	* Stats
	*/
	public function getAverage() 
	{
		if ($this->PageID) {
			return round(RatingFeedback::get()->filter('PageID', $this->PageID)->avg('Rating'), 2);
		}
	}

	public function getTotalVotes() 
	{
		if ($this->PageID) {
			return RatingFeedback::get()->filter('PageID', $this->PageID)->count();
		}
	}

}