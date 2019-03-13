<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class InboxController extends Controller
{

    protected function getImapClient()
    {

        $credentials = Session::get('credentials');

        return \App::make('Imap\Connection\GMail')
            ->setUsername($credentials['email'])
            ->setPassword($credentials['password'])
            ->connect();

    }

    public function getInbox(Request $request)
    {
        $client = $this->getImapClient();
        $currentMailbox = $request->get('box', $client->getCurrentMailbox());
        $mailboxes = $client->getMailboxes();

        // se la mailbox corrente è tra quelle presenti nell'array e non è la mailbox corrente la setto come tale
        if($currentMailbox != $client->getCurrentMailbox() && in_array($currentMailbox, $mailboxes)){
            $client->setCurrentMailbox($currentMailbox);
        }

        $page = $request->get('page', 1);
        $messages = $client->getPage($page);



        //public function __construct($items, $total, $perPage, $currentPage = null, array $options = [])
        $paginator = new LengthAwarePaginator(
            $messages,              // elementi
            $client->getCount(),    // numero totale elementi
            25,             // per page
            $page,                  // pagina corrente
            [ 'path' => '/inbox']   // opzioni
        );

        return view('app.inbox', compact('messages', 'mailboxes', 'currentMailbox', 'paginator'));
    }

    public function getMessage(Request $request)
    {
        $client = $this->getImapClient();
        $currentMailbox = $request->get('box', $client->getCurrentMailbox());

        $mailboxes = $client->getMailboxes();

        if($currentMailbox != $client->getCurrentMailbox() && in_array($currentMailbox, $mailboxes)){
            $client->setCurrentMailbox($currentMailbox);
        }

        $messageId = $request->route('id');
        $message = $client->getMessage($messageId)->fetch();

        return view('app.read', compact('currentMailbox', 'mailboxes', 'message'));
    }

    public function getAttachment(Request $request)
    {
        $client = $this->getImapClient();
        $messageId = $request->route('id');
        $attachementPart = $request->route('partId');

        $message = $client->getMessage($messageId)->fetch();
        $attachment = $message->getAttachmentByPartId($attachementPart)->fetch();

        // creaiamo una custom HTTP response con il file da scaricare
        //response()->make(body della risposta, status della risposta, header)
        return response()->make($attachment->getData(), 200, [
            'Content-Type' => $attachment->getMimeType(),
            'Content-Disposition' => "attachment; filename=\"{$attachment->getFilename()}\""
            // Questa intestazione informa il browser che non deve tentare di aprire immediatamente ed elaborare la
            // risposta, ma piuttosto di salvare la risposta come file utilizzando il nome file specificato nell'intestazione
        ]);
    }

    public function getDelete(Request $request)
    {
        $client = $this->getImapClient();
        $messageId = $request->route('id');
        $client->deleteMessage($messageId);

        return redirect('inbox')->with('success', "Message Deleted");
    }

    public function getCompose(Request $request)
    {
        $client = $this->getImapClient();
        $mailboxes = $client->getMailboxes();
        $messageId = $request->route('id');

        $quotedMessage = '';
        $message = null;

        if(!is_null($messageId)){
            $message = $client->getMessage($messageId)->fetch();
            $quotedMessage = $message->getPlainBody();
            $messageLines = explode("\n", $quotedMessage);

            foreach($messageLines as &$line){
                $line = '> ' . $line;
            }

            $quotedMessage = implode("\n", $messageLines);
        }

        return view('app.compose', compact('quotedMessage', 'message', 'mailboxes'));

    }

    public function postSend(Request $request)
    {
        $this->validate($request, [
            'from' => 'required|email',
            'to' => 'required|email',
            'subject' => 'required|max:255',
            'message' => 'required'
        ]);

        $from = $request->input('from');
        $to = $request->input('to');
        $subject = $request->input('subject');
        $message = $request->input('message');

        Mail::raw($message, function($messageObj) use ($to, $from, $subject) {
            $messageObj->from($from);
            $messageObj->to($to);
            $messageObj->subject($subject);
        });

        return redirect('inbox')->with('success', 'Message Sent!');
    }
}