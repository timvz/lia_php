<?php

//User the following constants for the get_permalink function
//e.g. get_permalink(Permalink::ViewProfile)
abstract class Permalink
{
	const ViewProfile 		= 194;
  	const EditProfile 		= 178;
  	const Applicants 		= 229;
  	const FindApplicants 	= 181;
  	const ViewHistory 		= 234; // Page Title: Requests
  	const ViewRequest		= 200;
  	const AddRequest  		= 216; // Page Title: Enter Assistance Request
	const DeleteApplicant	= 247;
	const RecordCounts 		= 273;
	const SearchResults		= 315; // 324 on Test Site, 315 on Live Site
	const OrganizeUsers		= 330; // 285 on Test Site, 330 on Live Site
	const Organization		= 328; // 334 on Test Site, 328 on Live Site
	const SetUserOrgScript	= 332; // 330 on Test Site, 332 on Live Site
}

?>
