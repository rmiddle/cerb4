<?php

class ChCoreEmailSignatureTemplate extends Extension_EmailSignatureTemplate {
  function __construct($manifest) {
    $this->DevblocksExtension($manifest,1);
  }

  function run($signature) {
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
    $list['Worker'] = array('#first_name#' => 'First Name', '#last_name#' => 'Last Name','#title#' => 'Title');
    return;
  }
};

class ChCoreAutoReplyNew extends Extension_AutoReplyNew {
  function __construct($manifest) {
    $this->DevblocksExtension($manifest,1);
  }

  function run(CerberusTicket $ticket, $properties) {
    $content = $properties['content'];
    $properties['content'] = str_replace(
      array('#ticket_id#','#mask#','#subject#','#timestamp#', '#sender#','#sender_first#','#orig_body#'),
      array($id, $sMask, $sSubject, date('r'), $fromAddressInst->email, $fromAddressInst->first_name, ltrim($message->body)),
      $content
    );
  }
  
  function render($list) {
    $list['General'] = array('#timestamp#' => 'Current Time');
    $list['First Requester'] = array('#sender#' => 'E-mail', '#sender_first#' => 'First Name');
    $list['First Message'] = array('#orig_body#' => 'Message Body');
    $list['Ticket'] = array('#mask#' => 'Reference ID', '#ticket_id#' => 'Internal ID','#subject#' => 'Subject');
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
    $list['General'] = array('#timestamp#' => 'Current Time');
    $list['First Requester'] = array('#sender#' => 'E-mail', '#sender_first#' => 'First Name');
    $list['First Message'] = array('#orig_body#' => 'Message Body');
    $list['Ticket'] = array('#mask#' => 'Reference ID', '#ticket_id#' => 'Internal ID','#subject#' => 'Subject');
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
    $list['General'] = array('#timestamp#' => 'Current Time');
    if (2==$type) {
      $list['Sender'] = array('#sender_first_name#' => 'First Name', '#sender_last_name#' => 'Last Name','#sender_org#' => 'Organization');
      $list['Ticket'] = array('#mask#' => 'Reference ID', '#ticket_id#' => 'Internal ID','#subject#' => 'Subject');
    }
    $list['Worker'] = array('#first_name#' => 'First Name', '#last_name#' => 'Last Name','#title#' => 'Title');
    return;
  }
};

