<?php

namespace DNADesign\RatingFeedback\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\Parsers\URLSegmentFilter;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

/**
* This extension provides a SiteTree object with the options to activate the FeedbackRating functionality.
*/
class SiteTree_EnableRatingFeedback extends DataExtension {

	private static $db = [
		// Override global var
		'RatingBlockMaxStars' => 'Int',
		'RatingBlockTitle' => 'Varchar(255)',
		'RatingBlockIntro' => 'HTMLText',
		'RatingBlockSuccess' => 'HTMLText',
		'RatingBlockSpamProtection' => 'Enum("Default, Enabled, Disabled")',
		// Block level var		
		'EnableRatingFeedback' => "Enum('Disabled, Rating and Feedback, Rating only, Feedback only')",
		'HideCommentField' => 'Boolean',
		'RequireComment' => "Enum('No, Always, If rating is less than')",
		'RequireCommentIfRatingLessThan' => 'Int'
	];

	public function updateCMSFields(FieldList $fields)
	{
		// Display Feedback options
		$enableFeedback = DropdownField::create('EnableRatingFeedback', 'Feedback/Rating', $this->owner->dbObject('EnableRatingFeedback')->enumValues());
		
		$config = SiteConfig::current_site_config();
		// Allow to override default max star
		$maxStar = NumericField::create('RatingBlockMaxStars', 'Rating/Feedback Stars')
				->setRightTitle('Default Rating/Feedback Stars used if left blank');
		// Allow to hide comment field by default
		$hideComment = CheckboxField::create('HideCommentField')
				->setRightTitle('Hide comment field until a rating as been selected.')
				->displayIf('EnableRatingFeedback')->isEqualTo('Rating and Feedback')->end();
		// Require comment field
		$requireComment = DropdownField::create('RequireComment', 'Require Comment', $this->owner->dbObject('RequireComment')->enumValues())
				->displayIf('EnableRatingFeedback')->isEqualTo('Rating and Feedback')->end();
		// Ste the number of star below which the comment field should be required
		$minRating = NumericField::create('RequireCommentIfRatingLessThan')
				->displayIf('EnableRatingFeedback')->isEqualTo('Rating and Feedback')->andIf('RequireComment')->isEqualTo('If rating is less than')->end();
		// Override Title
		$title = TextField::create('RatingBlockTitle', 'Rating/Feedback Block Title')
				->setRightTitle('Default Rating/Feedback Block Title used if left blank');
		// Override Intro
		$intro = HTMLEditorField::create('RatingBlockIntro', 'Rating/Feedback Block Intro')->setRows(3)
				->setRightTitle('Default Rating/Feedback Block Intro used if left blank');
		// Override Success message
		$success = HTMLEditorField::create('RatingBlockSuccess', 'Rating/Feedback Block Success message')->setRows(3)
				->setRightTitle('Default Rating/Feedback Block Success Message used if left blank');
		// Override Spam enable
		$spam = OptionsetField::create('RatingBlockSpamProtection', 'Rating/Feedback: Enable Spam Protection', $this->owner->dbObject('RatingBlockSpamProtection')->enumValues(), $this->owner->RatingBlockSpamProtection);

		$fields->addFieldsToTab('Root.Feedback', [$enableFeedback, $maxStar, $hideComment, $requireComment, $minRating,  $title, $intro, $success, $spam]);	
	}

	/**
	* By default, enum will not have any value in DB
	* so this helps figure out if it is disabled or not
	*
	* @return Boolean
	*/
	public function getRatingFeedbackEnabled()
	{
		return ($this->owner->EnableRatingFeedback !== null && $this->owner->EnableRatingFeedback !== 'Disabled');
	}

	public function includeRating()
	{
		return ($this->owner->EnableRatingFeedback == 'Rating and Feedback' || $this->owner->EnableRatingFeedback == 'Rating only');
	}

	public function includeFeedback()
	{
		return ($this->owner->EnableRatingFeedback == 'Rating and Feedback' || $this->owner->EnableRatingFeedback == 'Feedback only');
	}

	/**
	* Comment is required if it is the ony field
	* Or if the RequireComment option is tick
	*/
	public function isFeedbackRequired()
	{
		return ($this->owner->EnableRatingFeedback == 'Feedback only' || ($this->owner->EnableRatingFeedback == 'Rating and Feedback' && $this->owner->RequireComment == 'Always'));
	}

	/**
	* Comment field can be required if stars are required
	* and comment is not required by default
	* and the minimun star is less than the maximum star
	* Note: javascript toggles the required attribute
	*/
	public function includeRequireFeedbackIfRatingLessThanAttribute()
	{
		return ($this->owner->EnableRatingFeedback == 'Rating and Feedback' && $this->owner->RequireComment == 'If rating is less than' && $this->owner->RequireCommentIfRatingLessThan > 0 && $this->owner->getBlockMaxStars() >= $this->owner->RequireCommentIfRatingLessThan);
	}

	public function getBlockMaxStars()
	{
		return ($this->owner->RatingBlockMaxStars) ? $this->owner->RatingBlockMaxStars : (int) SiteConfig::current_site_config()->DefaultRatingBlockMaxStars;
	}

	public function getBlockTitle()
	{
		return ($this->owner->RatingBlockTitle) ? $this->owner->RatingBlockTitle : SiteConfig::current_site_config()->DefaultRatingBlockTitle;
	}

	public function getBlockIntro()
	{
		return ($this->owner->RatingBlockIntro) ? $this->owner->RatingBlockIntro : SiteConfig::current_site_config()->DefaultRatingBlockIntro;
	}

	public function getBlockSuccess()
	{
		return ($this->owner->RatingBlockSuccess) ? $this->owner->RatingBlockSuccess : SiteConfig::current_site_config()->DefaultRatingBlockSuccess;
	}

	public function enableSpamProtection()
	{
		// If default, fall back on site config
		if (!$this->owner->RatingBlockSpamProtection || $this->owner->RatingBlockSpamProtection == 'Default') {
			return SiteConfig::current_site_config()->DefaultRatingBlockSpamProtect;
		}
		// Otherwise evaluate value as boolean
		else {
			return $this->owner->RatingBlockSpamProtection == 'Enabled';
		}
	}

	/**
	* To set a data-attibute on the form
	* for javascript function
	*/
	public function getRatingType()
	{
		$filter = URLSegmentFilter::create();
		$t = $filter->filter($this->owner->EnableRatingFeedback);

		return $t;
	}

	public function getHTMLID()
	{
		return sprintf('rating%s', $this->owner->ID);
	}
}