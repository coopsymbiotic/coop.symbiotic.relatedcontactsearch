CiviCRM extension that helps find related contacts with specific relationship types and allow to create groups with them.

## Installation

1. Install the extension in CiviCRM
2. You should see a new Custom Search "Related Contact Search" and can use it like any other search


## Usage

The main goal is to be able to create a group of targeted organizations but to send emails to the representative of those organization.

You often end up :
* adding the main contact email in the organization contact but it's not ideal because it won't be possible to choose which contacts role for an organization you want to reach in this context (Director, Communication, Accounting, HR, Employees, ...)
* search for individual but you won't be able to filter by the organization details

With this extension :
* create as much relationship type you need to represent the different organization roles
* search for the organization contacts you want to reach and add them to a group
* use the new search, add you group (or groups with include / exclude functionality) and choose which relationship you wants
* create a new mass mailing based on the result or create a smart group if you want to send the email later or reuse that group

## Known limits

* include / exclude doesn't work with parent group -- see also https://issues.civicrm.org/jira/browse/CRM-15049 as we use a similar code as Include / Exclude search
* no way to prioritize one role over another (take role #1 if exist otherwise role #2)

