<?php

class ChCoreEmailSignatureTemplate extends Extension_EmailSignatureTemplate {
  function __construct($manifest) {
    $this->DevblocksExtension($manifest,1);
  }

  function run($ticket_id, $signature) {
    $active_worker = CerberusApplication::getActiveWorker();
    $worker = DAO_Worker::getAgent($active_worker->id); // Use the most recent info (not session)

    $sig = $signature;
    $signature = str_replace(
      array('#first_name#','#last_name#','#title#'),
      array($worker->first_name,$worker->last_name,$worker->title),
      $sig
    );
  }
  
  function render($list) {
		$translate = DevblocksPlatform::getTranslationService();
		
    $list['Worker'] = array(
			'#first_name#' => $translate->_('worker.first_name'), 
			'#last_name#' => $translate->_('worker.last_name'),
			'#title#' => $translate->_('worker.title')
			);
    return;
  }
};

class ChCoreAutoReplyNew extends Extension_AutoReplyNew {
  function __construct($manifest) {
    $this->DevblocksExtension($manifest,1);
  }

  function run(CerberusTicket $ticket, $properties) {
    $content = $properties['content'];
		$message = DAO_Ticket::getMessage($ticket->first_message_id);
		$address = DAO_Ticket::getRequestersByTicket($ticket->id);
    $properties['content'] = str_replace(
      array('#ticket_id#','#mask#','#subject#','#timestamp#', '#sender#','#sender_first#','#orig_body#'),
      array($ticket->id, $ticket->mask, $ticket->subject, date('r'), $address->email, $address->first_name, ltrim($message->body)),
      $content
    );
  }
  
	function render($list) {
		$translate = DevblocksPlatform::getTranslationService();
		
		$list['General'] = array('#timestamp#' => $translate->_('template.common.current_time'));
		$list['First Requester'] = array(
			'#sender#' => $translate->_('common.email'),
			'#sender_first#' => $translate->_('address.first_name')
			);
		$list['First Message'] = array('#orig_body#' => $translate->_('template.common.message_body'));
		$list['Ticket'] = array(
			'#mask#' => $translate->_('template.common.reference_id'),
			'#ticket_id#' => $translate->_('template.common.internal_id'),
			'#subject#' => $translate->_('message.header.subject')
			);
		return;
	}
};

class ChCoreAutoReplyClose extends Extension_AutoReplyClose {
  function __construct($manifest) {
    $this->DevblocksExtension($manifest,1);
  }

  function run(CerberusTicket $ticket, $properties) {
    $content = $properties['content'];
    $group_settings = DAO_GroupSettings::getSettings();

    if(null != ($msg_first = DAO_Ticket::getMessage($ticket->first_message_id))) {
      // First sender
      $ticket_sender = '';
      $ticket_sender_first = '';
      if(null != ($sender_first = DAO_Address::get($msg_first->address_id))) {
        $ticket_sender = $sender_first->email;
        $ticket_sender_first = $sender_first->first_name;
      }

      // First body
      $ticket_body = $msg_first->getContent();
    }

    $properties['content'] = str_replace(
      array('#ticket_id#', '#mask#','#subject#','#timestamp#','#sender#','#sender_first#','#orig_body#'),
      array($ticket->id, $ticket->mask, $ticket->subject, date('r'), $ticket_sender, $ticket_sender_first, ltrim($ticket_body)),
      $content
    );
  }
  
  function render($list) {
		$translate = DevblocksPlatform::getTranslationService();
		
		$list['General'] = array('#timestamp#' => $translate->_('template.common.current_time'));
		$list['First Requester'] = array(
			'#sender#' => $translate->_('common.email'),
			'#sender_first#' => $translate->_('address.first_name')
			);
		$list['First Message'] = array('#orig_body#' => $translate->_('template.common.message_body'));
		$list['Ticket'] = array(
			'#mask#' => $translate->_('template.common.reference_id'),
			'#ticket_id#' => $translate->_('template.common.internal_id'),
			'#subject#' => $translate->_('message.header.subject')
			);
    return;
  }
};

class ChCoreEmailTemplate extends Extension_EmailTemplate {
  function __construct($manifest) {
    $this->DevblocksExtension($manifest,1);
  }

  function run($message_id, $template) {
    $raw = $template;
    $replace = array();
    $with = array();

    $replace[] = '#timestamp#';
    $with[] = date('r');

    if(!empty($message_id)) {
      $message = DAO_Ticket::getMessage($message_id);
      $ticket = DAO_Ticket::getTicket($message->ticket_id);
      $sender = DAO_Address::get($message->address_id);
      $sender_org = DAO_ContactOrg::get($sender->contact_org_id);

      $replace[] = '#sender_first_name#';
      $replace[] = '#sender_last_name#';
      $replace[] = '#sender_org#';

      $with[] = $sender->first_name;
      $with[] = $sender->last_name;
      $with[] = (!empty($sender_org)?$sender_org->name:"");

      $replace[] = '#ticket_id#';
      $replace[] = '#ticket_mask#';
      $replace[] = '#ticket_subject#';

      $with[] = $ticket->id;
      $with[] = $ticket->mask;
      $with[] = $ticket->subject;
    }

    if(null != ($active_worker = CerberusApplication::getActiveWorker())) {
      $worker = DAO_Worker::getAgent($active_worker->id); // most recent info (not session)

      $replace[] = '#worker_first_name#';
      $replace[] = '#worker_last_name#';
      $replace[] = '#worker_title#';

      $with[] = $worker->first_name;
      $with[] = $worker->last_name;
      $with[] = $worker->title;
    }

    $template = str_replace($replace, $with, $raw);
  }
  
  function render($type, $list) {
		$translate = DevblocksPlatform::getTranslationService();
		
		$list['General'] = array('#timestamp#' => $translate->_('template.common.current_time'));
		if (2==$type) {
      $list['Sender'] = array(
				'#sender_first#' => $translate->_('address.first_name'),
				'#sender_last_name#' =>  $translate->_('address.last_name'),
				'#sender_org#' => $translate->_('address.contact_org_id')
				);
			$list['Ticket'] = array(
				'#mask#' => $translate->_('template.common.reference_id'),
				'#ticket_id#' => $translate->_('template.common.internal_id'),
				'#subject#' => $translate->_('message.header.subject')
				);
    }
    $list['Worker'] = array(
			'#first_name#' => $translate->_('worker.first_name'), 
			'#last_name#' => $translate->_('worker.last_name'),
			'#title#' => $translate->_('worker.title')
			);
    return;
  }
};

