<?php

namespace App\Http\Controllers;

use App\Services\IssueCreator;
use App\Ticket;

class TicketsIssueController extends Controller{

    public function store(IssueCreator $issueCreator, Ticket $ticket) {
        $this->authorize("create-issue", $ticket);
        $repository = request('repository');
        $bodyHeader = "Issue from ticket: " . route('tickets.show', $ticket);
        $issue      = $issueCreator->createIssue( $repository, $ticket->title,  $bodyHeader . "   " . $ticket->body);
        $ticket->addNote( auth()->user(), "Issue created to {$repository} with id #{$issue->id}");
        return back();
    }
}