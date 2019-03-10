<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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

        if($currentMailbox != $client->getCurrentMailbox() && in_array($currentMailbox, $mailboxes)){
            $client->setCurrentMailbox($currentMailbox);
        }

        $page = $request->get('page', 1);
        $messages = $client->getPage($page);

        $paginator = new LengthAwarePaginator(
            $messages,
            $client->getCount(),
            25,
            $page, [
            'path' => '/inbox'
        ]);

        return view('app.inbox', compact('messages', 'mailboxes', 'currentMailbox', 'paginator'));
    }
}
