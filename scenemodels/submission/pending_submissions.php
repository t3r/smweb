<?php

// Inserting libs
require_once '../autoload.php';
$requestDaoRO = \dao\DAOFactory::getInstance()->getRequestDaoRO();

// Get pending requests
$requestsArray = $requestDaoRO->getPendingRequests();
$requests = $requestsArray["ok"];
$invalidRequests = $requestsArray["failed"];

// Sets the time to UTC.
date_default_timezone_set('UTC');
$dtg = date('l jS \of F Y h:i:s A');

if (!empty($requests) || !empty($invalidRequests)) {
    $emailSubmit = \email\PendingRequestsEmailFactory::getPendingRequestsEmailContent($requests, $invalidRequests);
} else {
    $emailSubmit = \email\PendingRequestsEmailFactory::getPendingRequestsNoneEmailContent();
}
$emailSubmit->sendEmail("", true);

?>