<?php

namespace DNADesign\RatingFeedback\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;

/**
* This extensions provides the necessary interface to
* configure the RatingFeedback block
*/
class SiteConfig_RatingFeedbackOptions extends DataExtension
{
    private static $db = [
        'DefaultRatingBlockMaxStars' => 'Int',
        'DefaultRatingBlockTitle' => 'Varchar(255)',
        'DefaultRatingBlockIntro' => 'HTMLText',
        'DefaultRatingBlockSuccess' => 'HTMLText',
        'DefaultRatingBlockSpamProtect' => 'Boolean'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.RatingFeedbackOptions', [
            // Number of stars
            NumericField::create('DefaultRatingBlockMaxStars', 'Default Rating/Feedback Stars')
                ->setRightTitle('Defaults to 5'),
            // Title
            TextField::create('DefaultRatingBlockTitle', 'Default Rating/Feedback Block Title'),
            // Intorduction
            HTMLEditorField::create('DefaultRatingBlockIntro', 'Default Rating/Feedback Block Intro')
                ->setRows(3),
            // Success message
            HTMLEditorField::create('DefaultRatingBlockSuccess', 'Default Rating/Feedback Block Success Message')
                ->setRows(3),
            // Spam protection
            CheckboxField::create('DefaultRatingBlockSpamProtect', 'Rating/Feedback: Enable Spam Protection by default')
        ]);
    }
}
