<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*
 * IMPORTANT LICENSING NOTE from your friends on the Cerberus Helpdesk Team
 * 
 * Sure, it would be so easy to just cheat and edit this file to use the 
 * software without paying for it.  But we trust you anyway.  In fact, we're 
 * writing this software for you! 
 * 
 * Quality software backed by a dedicated team takes money to develop.  We 
 * don't want to be out of the office bagging groceries when you call up 
 * needing a helping hand.  We'd rather spend our free time coding your 
 * feature requests than mowing the neighbors' lawns for rent money. 
 * 
 * We've never believed in encoding our source code out of paranoia over not 
 * getting paid.  We want you to have the full source code and be able to 
 * make the tweaks your organization requires to get more done -- despite 
 * having less of everything than you might need (time, people, money, 
 * energy).  We shouldn't be your bottleneck.
 * 
 * We've been building our expertise with this project since January 2002.  We 
 * promise spending a couple bucks [Euro, Yuan, Rupees, Galactic Credits] to 
 * let us take over your shared e-mail headache is a worthwhile investment.  
 * It will give you a sense of control over your in-box that you probably 
 * haven't had since spammers found you in a game of "E-mail Address 
 * Battleship".  Miss. Miss. You sunk my in-box!
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas and the warm fuzzy 
 * feeling of feeding a couple obsessed developers who want to help you get 
 * more done than 'the other guy'.
 *
 * - Jeff Standen, Mike Fogg, Brenan Cavish, Darren Sugita, Dan Hildebrandt
 * 		and Joe Geck.
 *   WEBGROUP MEDIA LLC. - Developers of Cerberus Helpdesk
 */
class CerberusBayes {
	const PROBABILITY_CEILING = 0.9999;
	const PROBABILITY_FLOOR = 0.0001;
	const PROBABILITY_UNKNOWN = 0.4;
	const PROBABILITY_MEDIAN = 0.5;
	const MAX_INTERESTING_WORDS = 15;
	const MAX_BODY_LENGTH = 15000;
	
	/**
	 * @param string $text A string of text to break into unique words
	 * @param integer $min The minimum word length used
	 * @param integer $max The maximum word length used
	 * @return array An array with unique words as keys
	 */
	static function parseUniqueWords($text,$min=3,$max=24) {
		$chars = array('\'');
		$tokens = array('__apos__');
		
		// Encode apostrophes/etc
		$text = str_replace($chars,$tokens,$text);
		
		// Force lowercase and strip non-word punctuation (a-z, 0-9, _)
		if(function_exists('mb_ereg_replace'))
			$text = mb_ereg_replace('[^a-z0-9_]+', ' ', mb_convert_case($text, MB_CASE_LOWER));
		else
			$text = preg_replace('/[^a-z0-9_]+/', ' ', mb_convert_case($text, MB_CASE_LOWER));

		// Decode apostrophes/etc
		$text = str_replace($tokens,$chars,$text);

		// Sort unique words w/ condensed spaces
		if(function_exists('mb_ereg_replace'))
			$words = array_flip(explode(' ', mb_ereg_replace('\s+', ' ', $text)));
		else 
			$words = array_flip(explode(' ', preg_replace('/\s+/', ' ', $text)));

		// Toss words that are too common
	    $words = self::_removeCommonWords($words);
		
		// Toss anything over/under the word length bounds
		foreach($words as $k => $v) {
			$len = mb_strlen($k);
			if($len < $min || $len > $max || is_numeric($k)) { // [TODO]: Make decision on !numeric?
				unset($words[$k]); // toss
			}
		}

		return $words;
	}
	
	static private function _removeCommonWords($words) {
	    // English
	    unset($words['']);
	    unset($words['a']);
	    unset($words['about']);
	    unset($words['am']);
	    unset($words['an']);
	    unset($words['and']);
	    unset($words['any']);
	    unset($words['as']);
	    unset($words['at']);
	    unset($words['are']);
	    unset($words['be']);
	    unset($words['been']);
	    unset($words['but']);
	    unset($words['can']);
	    unset($words['could']);
//	    unset($words['com']);
	    unset($words['did']);
	    unset($words['do']);
	    unset($words['doesn\'t']);
	    unset($words['don\'t']);
	    unset($words['for']);
	    unset($words['from']);
	    unset($words['get']);
	    unset($words['had']);
	    unset($words['has']);
	    unset($words['have']);
	    unset($words['how']);
//	    unset($words['http']);
	    unset($words['i']);
	    unset($words['i\'m']);
	    unset($words['if']);
	    unset($words['in']);
	    unset($words['into']);
	    unset($words['is']);
	    unset($words['it']);
	    unset($words['it\'s']);
	    unset($words['me']);
	    unset($words['my']);
	    unset($words['not']);
	    unset($words['of']);
	    unset($words['on']);
	    unset($words['than']);
	    unset($words['that']);
	    unset($words['the']);
	    unset($words['their']);
	    unset($words['them']);
	    unset($words['then']);
	    unset($words['there']);
	    unset($words['these']);
	    unset($words['they']);
	    unset($words['this']);
	    unset($words['to']);
	    unset($words['was']);
	    unset($words['we']);
	    unset($words['were']);
	    unset($words['what']);
	    unset($words['when']);
	    unset($words['which']);
	    unset($words['while']);
	    unset($words['why']);
	    unset($words['will']);
	    unset($words['with']);
	    unset($words['would']);
//	    unset($words['www']);
	    unset($words['you']);
	    unset($words['your']);
	    
	    return $words;
	}
	
	static function unaccentUtf8Text($text) {
		if(0 == strcasecmp(LANG_CHARSET_CODE,'utf-8')) {
			$from = array('À','Á','Â','Ã','Ä','Å','Æ','à','á','â','ã','ä','å','æ','Ò','Ó','Ô','Õ','Õ','Ö','Ø','ò','ó','ô','õ','ö','ø','È','É','Ê','Ë','è','é','ê','ë','ð','Ç','ç','Ð','Ì','Í','Î','Ï','ì','í','î','ï','Ù','Ú','Û','Ü','ù','ú','û','ü','Ñ','ñ','Þ','ß','ÿ','ý');
			$to = array('A','A','A','A','A','A','a','a','a','a','a','a','a','a','O','O','O','O','O','O','O','o','o','o','o','o','o','E','E','E','E','e','e','e','e','e','C','c','e','I','I','I','I','i','i','i','i','U','U','U','U','u','u','u','u','N','n','t','ss','y','y');
			$text = str_replace($from, $to, $text);
		}
		
		return $text;
	}
	
	/**
	 * @param string $text A string of text to run through spam scoring
	 * @return array Analyzed statistics
	 */
	static function processText($text) {
		$text = self::unaccentUtf8Text($text);
		$words = self::parseUniqueWords($text);
		$words = self::_lookupWordIds($words);
		$words = self::_analyze($words);
		return $words; 
	}
	
	static function markTicketAsSpam($ticket_id) {
		self::_markTicketAs($ticket_id, true);
	}
	
	static function markTicketAsNotSpam($ticket_id) {
		self::_markTicketAs($ticket_id, false);
	}
	
	// [TODO] Accept batch tickets for training for efficiencies sake
	static private function _markTicketAs($ticket_id,$spam=true) {
		// [TODO] Make sure we can't retrain tickets which are already spam trained
		// [TODO] This is a performance killer
		$ticket = DAO_Ticket::getTicket($ticket_id);
		
		if($ticket->spam_training != CerberusTicketSpamTraining::BLANK)
			return TRUE;
			
		// pull up text of first ticket message
		// [TODO] This is a performance killer
		$first_message = DAO_Ticket::getMessage($ticket->first_message_id);

		if(empty($first_message)) 
		    return FALSE;
		
		// [TODO] This is a performance killer
		$headers = DAO_MessageHeader::getAll($first_message->id);
		    
		// Pass text to analyze() to get back interesting words
		$content = '';
		if(!empty($ticket->subject)) {
			// SplitCamelCapsSubjects
			$hits = preg_split("{(?<=[a-z])(?=[A-Z])}x", $ticket->subject);
			if(is_array($hits) && !empty($hits)) {
				$content .= implode(' ',$hits);
			}
		}
		$content .= ' ' . $first_message->getContent();
		
		if(strlen($content) > self::MAX_BODY_LENGTH)
	    	$content = substr($content, 0, strrpos(substr($content, 0, self::MAX_BODY_LENGTH), ' '));
		
		$words = self::processText($content);
		
		// Train interesting words as spam/notspam
//		$out = self::_calculateSpamProbability($words);
//		self::_trainWords($out['words'],$spam);
		self::_trainWords($words,$spam); // [TODO] Testing, train all words
		
		// Increase the bayes_stats spam or notspam total count by 1
		// [TODO] This is a performance killer (could be done in batches)
		if($spam) {
		    DAO_Bayes::addOneToSpamTotal();
		    DAO_Address::addOneToSpamTotal($ticket->first_wrote_address_id); 
		} else {
		    DAO_Bayes::addOneToNonSpamTotal();
		    DAO_Address::addOneToNonSpamTotal($ticket->first_wrote_address_id);
		}
		
		// Forced training should leave a cache of 0.0001 or 0.9999 on the ticket table
		$fields = array(
			'spam_score' => ($spam) ? 0.9999 : 0.0001,
			'spam_training' => ($spam) ? CerberusTicketSpamTraining::SPAM : CerberusTicketSpamTraining::NOT_SPAM,
		);
		DAO_Ticket::updateTicket($ticket_id,$fields);

		return TRUE;
	}

	/**
	 * @param CerberusBayesWord[] $words
	 * @param boolean $spam
	 */
	static private function _trainWords($words, $spam=true) {
		if(!is_array($words))
		    return;
	
		$ids = array();
		foreach($words as $word) { /* @var $word CerberusBayesWord */
		    $ids[] = $word->id;
		}
		    
		if($spam) {
		    DAO_Bayes::addOneToSpamWord($ids); 
		} else { 
		    DAO_Bayes::addOneToNonSpamWord($ids);
		}
		
		unset($ids);
	}
	
	/**
	 * @param array $words An array indexed with words to look up 
	 */
	static private function _lookupWordIds($words) {
		$pos = 0;
		$batch_size = 100; //[TODO] Tune this
		$outwords = array(); // 
				
		while(array() != ($batch = array_slice($words,$pos,$batch_size,true))) {
			$batch = array_keys($batch); // words are now values
			$word_ids = DAO_Bayes::lookupWordIds($batch);
			$outwords = array_merge($outwords, $word_ids);
			$pos += $batch_size;
		}
		
		return $outwords;
	}
	
	static private function _analyze($words) {
		foreach($words as $k => $w) {
			$words[$k]->probability = self::calculateWordProbability($w);
			
			// [JAS]: If a word appears more than 5 times (counting weight) in the corpus, use it.  Otherwise de-emphasize.
			if(($w->nonspam * 1) + $w->spam >= 5)
				$words[$k]->interest_rating = self::_getMedianDeviation($w->probability);
			else
				$words[$k]->interest_rating = 0.00;
		}
		
		return $words;
	}
	
	static public function _combineP($argv) {
		// [JAS]: Variable for all our probabilities multiplied, for Naive Bayes
		$AB = 1; // probabilities: A*B*C...
		$ZY = 1; // compliments: (1-A)*(1-B)*(1-C)...
		
		foreach($argv as $v) {
			$AB *= $v;
			$ZY *= (1-$v);
		}

		$combined_p = $AB / ($AB + $ZY);
		
		switch($combined_p)
		{
			case $combined_p > self::PROBABILITY_CEILING:
				return self::PROBABILITY_CEILING;
				break;
			case $combined_p < self::PROBABILITY_FLOOR:
				return self::PROBABILITY_FLOOR;
				break;
		}
		
		return number_format($combined_p,4);
	}
	
	/**
	 * @param float $p Probability
	 * @return float Median Deviation
	 */
	static private function _getMedianDeviation($p) {
		if($p > self::PROBABILITY_MEDIAN)
			return $p - self::PROBABILITY_MEDIAN;
		else
			return self::PROBABILITY_MEDIAN - $p;
	}
	
	/**
	 * @param CerberusBayesWord $word
	 * @return float The probability of the word being spammy.
	 */
	static public function calculateWordProbability(CerberusBayesWord $word) {
		static $stats = null; // [JAS]: [TODO] Keep an eye on this.
		if(is_null($stats)) $stats = DAO_Bayes::getStatistics();
		
		$ngood = max($stats['nonspam'],1);
		$nbad = max($stats['spam'],1);
		
		$g = intval($word->nonspam * 1);
		$b = intval($word->spam);

		// [JAS]: If less than 5 occurrences total
		if(($g*1 + $b) < 5) {
			$prob = self::PROBABILITY_UNKNOWN;
			
		} else {
			$prob = max(self::PROBABILITY_FLOOR,
				min(self::PROBABILITY_CEILING,
					floatval( 
						min(1,($b/$nbad))	
						/ 
						( $g/$ngood + $b/$nbad )
					)
				)
			);
		}
		
		return number_format($prob,4);
	}
	
	/**
	 * @param CerberusBayesWord $a
	 * @param CerberusBayesWord $b
	 */
	static private function _sortByInterest($a, $b) {
	   if ($a->interest_rating == $b->interest_rating) {
	       return 0;
	   }
	   return ($a->interest_rating < $b->interest_rating) ? -1 : 1;
	}
	
	/**
	 * @param CerberusBayesWord[] $words
	 * @return array 'probability' = Overall Spam Probability, 'words' = interesting words
	 */
	static private function _calculateSpamProbability($words) {
		$probabilities = array();
		
		// Sort words by interest descending
		$interesting_words = $words; 
		usort($interesting_words,array('CerberusBayes','_sortByInterest'));
		$interesting_words = array_slice($interesting_words,-1 * self::MAX_INTERESTING_WORDS);

		// Combine word probabilities into an overall probability
		foreach($interesting_words as $word) { /* @var $word CerberusBayesWord */
			$probabilities[] = $word->probability;
		}
		$combined = self::_combineP($probabilities);
		
		return array('probability' => $combined, 'words' => $interesting_words);
	}
	
	static function calculateTicketSpamProbability($ticket_id, $readonly=false) {
		// pull up text of first ticket message
	    $messages = DAO_Ticket::getMessagesByTicket($ticket_id);
	    $first_message = array_shift($messages);
	    $ticket = DAO_Ticket::getTicket($ticket_id);
	    
		if(empty($ticket) || empty($first_message) || !($first_message instanceOf CerberusMessage)) 
		    return FALSE;
		
		// Pass text to analyze() to get back interesting words
		$content = '';
		if(!empty($ticket->subject)) {
			// SplitCamelCapsSubjects
			$hits = preg_split("{(?<=[a-z])(?=[A-Z])}x", $ticket->subject);
			if(is_array($hits) && !empty($hits)) {
				$content .= implode(' ',$hits);
			}
		}
		$content .= ' ' . $first_message->getContent();		
		
		// Only check the first 15000 characters for spam, rounded to a sentence
		if(strlen($content) > self::MAX_BODY_LENGTH)
			$content = substr($content, 0, strrpos(substr($content, 0, self::MAX_BODY_LENGTH), ' '));
	    
		$words = self::processText($content);
		$out = self::_calculateSpamProbability($words);

		// Make a word list
	    $rawwords = array();
	    foreach($out['words'] as $k=>$v) { /* @var $v CerberusBayesWord */
	        $rawwords[] = $v->word;
	    }
		
		// Cache probability
		if(!$readonly) {
			$fields = array(
			    DAO_Ticket::SPAM_SCORE => $out['probability'],
			    DAO_Ticket::INTERESTING_WORDS => substr(implode(',',array_reverse($rawwords)),0,255),
			);
			DAO_Ticket::updateTicket($ticket_id, $fields);
		}
		
		return $out;
	}
	
};
?>
