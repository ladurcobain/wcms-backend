<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    //Auth
    $router->post('auth/login','AuthController@login');
    $router->post('auth/logout','AuthController@logout');
    $router->post('auth/change-password','AuthController@changePassword');
    $router->post('auth/update-profile','AuthController@updateProfile');

    //Activity
    $router->post('activity/get-log','ActivityController@getLog');
    $router->post('activity/get-dashboard','ActivityController@getDashboard');
    $router->post('activity/get-search-content','ActivityController@getSearchContent');
    $router->post('activity/get-file-manager','ActivityController@getFileManager');
    $router->post('activity/set-module','ActivityController@setModule');
    $router->post('activity/get-chart-line','ActivityController@getChartLine');
    $router->post('activity/get-visitor-by-day','ActivityController@getVisitorByDay');
    $router->post('activity/get-visitor-by-month','ActivityController@getVisitorByMonth');
    $router->post('activity/get-visitor-by-year','ActivityController@getVisitorByYear');
    $router->post('activity/get-visitor-by-satker','ActivityController@getVisitorBySatker');
    $router->post('activity/get-visitor','ActivityController@getVisitor');
    $router->post('activity/get-rating','ActivityController@getRating');
    $router->post('activity/get-contactus','ActivityController@getContactus');
    $router->post('activity/get-newsletter','ActivityController@getNewsletter');
    $router->post('activity/get-article','ActivityController@getArticle');
    $router->post('activity/get-summary','ActivityController@getsummary');
    $router->post('activity/get-annually','ActivityController@getAnnually');
    $router->post('activity/get-monthly','ActivityController@getMonthly');
    $router->post('activity/get-daily','ActivityController@getDaily');
    $router->post('activity/get-video-embed','ActivityController@getVideoEmbed');
    $router->post('activity/get-integrasi-jdih','ActivityController@getIntegrasiJdih');

    // Active
    $router->get('active/get-menu','ActiveController@getMenu');
    $router->get('active/get-role','ActiveController@getRole');
    $router->get('active/get-satker','ActiveController@getSatker');
    $router->get('active/get-tutorial','ActiveController@getTutorial');
    $router->get('active/get-integration','ActiveController@getIntegration');
    $router->get('active/get-pattern','ActiveController@getPattern');
    $router->get('active/get-cover','ActiveController@getCover');
    $router->post('active/get-user','ActiveController@getUser');
    $router->post('active/get-notification','ActiveController@getNotification');
    $router->post('active/get-messaging','ActiveController@getMessaging');
    $router->post('active/session-satker','ActiveController@sessionSatker');
    $router->post('active/all-satker','ActiveController@allSatker');
    $router->post('active/leveling-satker','ActiveController@levelingSatker');

    //User
    $router->post('user/get-all','UserController@getAll');
    $router->post('user/get-single','UserController@getSingle');
    $router->post('user/insert-data','UserController@insertData');
    $router->post('user/update-data','UserController@updateData');
    $router->post('user/update-password','UserController@updatePassword');
    $router->post('user/delete-data','UserController@deleteData');

    //Satker
    $router->post('satker/get-full','SatkerController@getFull');
    $router->post('satker/get-all','SatkerController@getAll');
    $router->post('satker/get-single','SatkerController@getSingle');
    $router->post('satker/insert-data','SatkerController@insertData');
    $router->post('satker/update-info','SatkerController@updateInfo');
    $router->post('satker/update-medsos','SatkerController@updateMedsos');
    $router->post('satker/update-support','SatkerController@updateSupport');
    $router->post('satker/update-videos','SatkerController@updateVideos');
    $router->post('satker/update-patterns','SatkerController@updatePatterns');
    $router->post('satker/update-backgrounds','SatkerController@updateBackgrounds');
    $router->post('satker/delete-data','SatkerController@deleteData');
    $router->post('satker/process-data','SatkerController@processData');
    $router->post('satker/get-access','SatkerController@getAccess');
    $router->post('satker/get-navigation','SatkerController@getNavigation');

    //Role-user
    $router->post('role-user/get-full','RoleUserController@getFull');
    $router->post('role-user/get-all','RoleUserController@getAll');
    $router->post('role-user/get-single','RoleUserController@getSingle');
    $router->post('role-user/insert-data','RoleUserController@insertData');
    $router->post('role-user/update-data','RoleUserController@updateData');
    $router->post('role-user/delete-data','RoleUserController@deleteData');
    $router->post('role-user/process-data','RoleUserController@processData');
    $router->post('role-user/get-access','RoleUserController@getAccess');
    $router->post('role-user/get-authority','RoleUserController@getAuthority');

    //Config-preferences
    $router->post('config/preference/get-single','ConfigPreferenceController@getSingle');
    $router->post('config/preference/update-data','ConfigPreferenceController@updateData');

    //Config-Integration
    $router->post('config/integration/get-single','ConfigIntegrationController@getSingle');
    $router->post('config/integration/update-data','ConfigIntegrationController@updateData');

    //Master-Module
    $router->post('master/module/get-all','MasterModuleController@getAll');
    $router->post('master/module/get-single','MasterModuleController@getSingle');
    $router->post('master/module/update-data','MasterModuleController@updateData');

    //Master-Menu
    $router->post('master/menu/get-all','MasterMenuController@getAll');
    $router->post('master/menu/get-single','MasterMenuController@getSingle');
    $router->post('master/menu/update-data','MasterMenuController@updateData');

    //Master-Tutorial
    $router->post('master/tutorial/get-all','MasterTutorialController@getAll');
    $router->post('master/tutorial/get-single','MasterTutorialController@getSingle');
    $router->post('master/tutorial/insert-data','MasterTutorialController@insertData');
    $router->post('master/tutorial/update-data','MasterTutorialController@updateData');
    $router->post('master/tutorial/delete-data','MasterTutorialController@deleteData');

    //Master-pattern
    $router->post('master/pattern/get-all','MasterPatternController@getAll');
    $router->post('master/pattern/get-single','MasterPatternController@getSingle');
    $router->post('master/pattern/insert-data','MasterPatternController@insertData');
    $router->post('master/pattern/update-data','MasterPatternController@updateData');
    $router->post('master/pattern/delete-data','MasterPatternController@deleteData');

    //Master-cover
    $router->post('master/cover/get-all','MasterCoverController@getAll');
    $router->post('master/cover/get-single','MasterCoverController@getSingle');
    $router->post('master/cover/insert-data','MasterCoverController@insertData');
    $router->post('master/cover/update-data','MasterCoverController@updateData');
    $router->post('master/cover/delete-data','MasterCoverController@deleteData');
    
    //Notification
    $router->post('notification/get-all','NotificationController@getAll');
    $router->post('notification/get-single','NotificationController@getSingle');
    $router->post('notification/process-data','NotificationController@processData');
    $router->post('notification/update-data','NotificationController@updateData');
    $router->post('notification/delete-data','NotificationController@deleteData');
    
    //Survey
    $router->post('survey/get-all','SurveyController@getAll');
    $router->post('survey/get-summary','SurveyController@getSummary');
    $router->post('survey/process-data','SurveyController@processData');
    $router->post('survey/delete-data','SurveyController@deleteData');
    $router->post('survey/get-single','SurveyController@getSingle');
    $router->post('survey/get-by-user','SurveyController@getByUser');

    //Rating
    $router->post('rating/get-all','RatingController@getAll');
    $router->post('rating/get-summary','RatingController@getSummary');
    $router->post('rating/process-data','RatingController@processData');
    $router->post('rating/delete-data','RatingController@deleteData');
    $router->post('rating/get-single','RatingController@getSingle');

    //Newsletter
    $router->post('newsletter/get-all','NewsletterController@getAll');
    $router->post('newsletter/get-summary','NewsletterController@getSummary');
    $router->post('newsletter/process-data','NewsletterController@processData');
    $router->post('newsletter/delete-data','NewsletterController@deleteData');
    $router->post('newsletter/get-single','NewsletterController@getSingle');

    //Contact-Us
    $router->post('contact-us/get-all','ContactUsController@getAll');
    $router->post('contact-us/get-summary','ContactUsController@getSummary');
    $router->post('contact-us/process-data','ContactUsController@processData');
    $router->post('contact-us/delete-data','ContactUsController@deleteData');
    $router->post('contact-us/get-single','ContactUsController@getSingle');


    //Home-Banner
    $router->post('home/banner/get-all','HomeBannerController@getAll');
    $router->post('home/banner/get-single','HomeBannerController@getSingle');
    $router->post('home/banner/insert-data','HomeBannerController@insertData');
    $router->post('home/banner/update-data','HomeBannerController@updateData');
    $router->post('home/banner/delete-data','HomeBannerController@deleteData');

    //Home-Infografis
    $router->post('home/infografis/get-all','HomeInfografisController@getAll');
    $router->post('home/infografis/get-single','HomeInfografisController@getSingle');
    $router->post('home/infografis/insert-data','HomeInfografisController@insertData');
    $router->post('home/infografis/update-data','HomeInfografisController@updateData');
    $router->post('home/infografis/delete-data','HomeInfografisController@deleteData');
    
    //Home-Related
    $router->post('home/related/get-all','HomeRelatedController@getAll');
    $router->post('home/related/get-single','HomeRelatedController@getSingle');
    $router->post('home/related/insert-data','HomeRelatedController@insertData');
    $router->post('home/related/update-data','HomeRelatedController@updateData');
    $router->post('home/related/delete-data','HomeRelatedController@deleteData');

    //About-Info
    $router->post('about/info/get-all','AboutInfoController@getAll');
    $router->post('about/info/get-single','AboutInfoController@getSingle');
    $router->post('about/info/insert-data','AboutInfoController@insertData');
    $router->post('about/info/update-data','AboutInfoController@updateData');
    $router->post('about/info/delete-data','AboutInfoController@deleteData');

    //About-Story
    $router->post('about/story/get-all','AboutStoryController@getAll');
    $router->post('about/story/get-single','AboutStoryController@getSingle');
    $router->post('about/story/insert-data','AboutStoryController@insertData');
    $router->post('about/story/update-data','AboutStoryController@updateData');
    $router->post('about/story/delete-data','AboutStoryController@deleteData');

    //About-Doctrin
    $router->post('about/doctrin/get-all','AboutDoctrinController@getAll');
    $router->post('about/doctrin/get-single','AboutDoctrinController@getSingle');
    $router->post('about/doctrin/insert-data','AboutDoctrinController@insertData');
    $router->post('about/doctrin/update-data','AboutDoctrinController@updateData');
    $router->post('about/doctrin/delete-data','AboutDoctrinController@deleteData');

    //About-Logo
    $router->post('about/logo/get-all','AboutLogoController@getAll');
    $router->post('about/logo/get-single','AboutLogoController@getSingle');
    $router->post('about/logo/insert-data','AboutLogoController@insertData');
    $router->post('about/logo/update-data','AboutLogoController@updateData');
    $router->post('about/logo/delete-data','AboutLogoController@deleteData');

    //About-iad
    $router->post('about/iad/get-all','AboutIadController@getAll');
    $router->post('about/iad/get-single','AboutIadController@getSingle');
    $router->post('about/iad/insert-data','AboutIadController@insertData');
    $router->post('about/iad/update-data','AboutIadController@updateData');
    $router->post('about/iad/delete-data','AboutIadController@deleteData');

    //About-Intro
    $router->post('about/intro/get-all','AboutIntroController@getAll');
    $router->post('about/intro/get-single','AboutIntroController@getSingle');
    $router->post('about/intro/insert-data','AboutIntroController@insertData');
    $router->post('about/intro/update-data','AboutIntroController@updateData');
    $router->post('about/intro/delete-data','AboutIntroController@deleteData');

    //About-Vision
    $router->post('about/vision/get-all','AboutVisionController@getAll');
    $router->post('about/vision/get-single','AboutVisionController@getSingle');
    $router->post('about/vision/insert-data','AboutVisionController@insertData');
    $router->post('about/vision/update-data','AboutVisionController@updateData');
    $router->post('about/vision/delete-data','AboutVisionController@deleteData');

    //About-Mision
    $router->post('about/mision/get-all','AboutMisionController@getAll');
    $router->post('about/mision/get-single','AboutMisionController@getSingle');
    $router->post('about/mision/insert-data','AboutMisionController@insertData');
    $router->post('about/mision/update-data','AboutMisionController@updateData');
    $router->post('about/mision/delete-data','AboutMisionController@deleteData');

    //About-Program
    $router->post('about/program/get-all','AboutProgramController@getAll');
    $router->post('about/program/get-single','AboutProgramController@getSingle');
    $router->post('about/program/insert-data','AboutProgramController@insertData');
    $router->post('about/program/update-data','AboutProgramController@updateData');
    $router->post('about/program/delete-data','AboutProgramController@deleteData');

    //About-Command
    $router->post('about/command/get-all','AboutCommandController@getAll');
    $router->post('about/command/get-single','AboutCommandController@getSingle');
    $router->post('about/command/insert-data','AboutCommandController@insertData');
    $router->post('about/command/update-data','AboutCommandController@updateData');
    $router->post('about/command/delete-data','AboutCommandController@deleteData');

    //Integrity-Legal
    $router->post('integrity/legal/get-all','IntegrityLegalController@getAll');
    $router->post('integrity/legal/get-single','IntegrityLegalController@getSingle');
    $router->post('integrity/legal/insert-data','IntegrityLegalController@insertData');
    $router->post('integrity/legal/update-data','IntegrityLegalController@updateData');
    $router->post('integrity/legal/delete-data','IntegrityLegalController@deleteData');

    //Integrity-Accountability
    $router->post('integrity/accountability/get-all','IntegrityAccountabilityController@getAll');
    $router->post('integrity/accountability/get-single','IntegrityAccountabilityController@getSingle');
    $router->post('integrity/accountability/insert-data','IntegrityAccountabilityController@insertData');
    $router->post('integrity/accountability/update-data','IntegrityAccountabilityController@updateData');
    $router->post('integrity/accountability/delete-data','IntegrityAccountabilityController@deleteData');

    //Integrity-Arrangement
    $router->post('integrity/arrangement/get-all','IntegrityArrangementController@getAll');
    $router->post('integrity/arrangement/get-single','IntegrityArrangementController@getSingle');
    $router->post('integrity/arrangement/insert-data','IntegrityArrangementController@insertData');
    $router->post('integrity/arrangement/update-data','IntegrityArrangementController@updateData');
    $router->post('integrity/arrangement/delete-data','IntegrityArrangementController@deleteData');

    //Integrity-Innovation
    $router->post('integrity/innovation/get-all','IntegrityInnovationController@getAll');
    $router->post('integrity/innovation/get-single','IntegrityInnovationController@getSingle');
    $router->post('integrity/innovation/insert-data','IntegrityInnovationController@insertData');
    $router->post('integrity/innovation/update-data','IntegrityInnovationController@updateData');
    $router->post('integrity/innovation/delete-data','IntegrityInnovationController@deleteData');

    //Integrity-Mechanism
    $router->post('integrity/mechanism/get-all','IntegrityMechanismController@getAll');
    $router->post('integrity/mechanism/get-single','IntegrityMechanismController@getSingle');
    $router->post('integrity/mechanism/insert-data','IntegrityMechanismController@insertData');
    $router->post('integrity/mechanism/update-data','IntegrityMechanismController@updateData');
    $router->post('integrity/mechanism/delete-data','IntegrityMechanismController@deleteData');

    //Integrity-Professionalism
    $router->post('integrity/professionalism/get-all','IntegrityProfessionalismController@getAll');
    $router->post('integrity/professionalism/get-single','IntegrityProfessionalismController@getSingle');
    $router->post('integrity/professionalism/insert-data','IntegrityProfessionalismController@insertData');
    $router->post('integrity/professionalism/update-data','IntegrityProfessionalismController@updateData');
    $router->post('integrity/professionalism/delete-data','IntegrityProfessionalismController@deleteData');

    //Integrity-Supervision
    $router->post('integrity/supervision/get-all','IntegritySupervisionController@getAll');
    $router->post('integrity/supervision/get-single','IntegritySupervisionController@getSingle');
    $router->post('integrity/supervision/insert-data','IntegritySupervisionController@insertData');
    $router->post('integrity/supervision/update-data','IntegritySupervisionController@updateData');
    $router->post('integrity/supervision/delete-data','IntegritySupervisionController@deleteData');

    //Contact-Medsos
    $router->post('contact/medsos/get-all','ContactMedsosController@getAll');
    $router->post('contact/medsos/get-single','ContactMedsosController@getSingle');
    $router->post('contact/medsos/insert-data','ContactMedsosController@insertData');
    $router->post('contact/medsos/update-data','ContactMedsosController@updateData');
    $router->post('contact/medsos/delete-data','ContactMedsosController@deleteData');

    //Conference-News
    $router->post('conference/news/get-all','ConferenceNewsController@getAll');
    $router->post('conference/news/get-single','ConferenceNewsController@getSingle');
    $router->post('conference/news/insert-data','ConferenceNewsController@insertData');
    $router->post('conference/news/update-data','ConferenceNewsController@updateData');
    $router->post('conference/news/delete-data','ConferenceNewsController@deleteData');
    $router->post('conference/news/get-new','ConferenceNewsController@getNew');


    //Information-Dpo
    $router->post('information/dpo/get-all','InformationDpoController@getAll');
    $router->post('information/dpo/get-single','InformationDpoController@getSingle');
    $router->post('information/dpo/insert-data','InformationDpoController@insertData');
    $router->post('information/dpo/update-data','InformationDpoController@updateData');
    $router->post('information/dpo/delete-data','InformationDpoController@deleteData');

    //Information-Unit
    $router->post('information/unit/get-all','InformationUnitController@getAll');
    $router->post('information/unit/get-single','InformationUnitController@getSingle');
    $router->post('information/unit/insert-data','InformationUnitController@insertData');
    $router->post('information/unit/update-data','InformationUnitController@updateData');
    $router->post('information/unit/delete-data','InformationUnitController@deleteData');

    //Information-Structural
    $router->post('information/structural/get-all','InformationStructuralController@getAll');
    $router->post('information/structural/get-single','InformationStructuralController@getSingle');
    $router->post('information/structural/insert-data','InformationStructuralController@insertData');
    $router->post('information/structural/update-data','InformationStructuralController@updateData');
    $router->post('information/structural/delete-data','InformationStructuralController@deleteData');

    //Information-Service
    $router->post('information/service/get-all','InformationServiceController@getAll');
    $router->post('information/service/get-single','InformationServiceController@getSingle');
    $router->post('information/service/insert-data','InformationServiceController@insertData');
    $router->post('information/service/update-data','InformationServiceController@updateData');
    $router->post('information/service/delete-data','InformationServiceController@deleteData');

    //Information-Infrastructure
    $router->post('information/infrastructure/get-all','InformationInfrastructureController@getAll');
    $router->post('information/infrastructure/get-single','InformationInfrastructureController@getSingle');
    $router->post('information/infrastructure/insert-data','InformationInfrastructureController@insertData');
    $router->post('information/infrastructure/update-data','InformationInfrastructureController@updateData');
    $router->post('information/infrastructure/delete-data','InformationInfrastructureController@deleteData');

    //Archive-Photo
    $router->post('archive/photo/get-all','ArchivePhotoController@getAll');
    $router->post('archive/photo/get-single','ArchivePhotoController@getSingle');
    $router->post('archive/photo/insert-data','ArchivePhotoController@insertData');
    $router->post('archive/photo/update-data','ArchivePhotoController@updateData');
    $router->post('archive/photo/delete-data','ArchivePhotoController@deleteData');

    //Archive-Regulation
    $router->post('archive/regulation/get-all','ArchiveRegulationController@getAll');
    $router->post('archive/regulation/get-single','ArchiveRegulationController@getSingle');
    $router->post('archive/regulation/insert-data','ArchiveRegulationController@insertData');
    $router->post('archive/regulation/update-data','ArchiveRegulationController@updateData');
    $router->post('archive/regulation/delete-data','ArchiveRegulationController@deleteData');

    //Archive-Movie
    $router->post('archive/movie/get-all','ArchiveMovieController@getAll');
    $router->post('archive/movie/get-single','ArchiveMovieController@getSingle');
    $router->post('archive/movie/insert-data','ArchiveMovieController@insertData');
    $router->post('archive/movie/update-data','ArchiveMovieController@updateData');
    $router->post('archive/movie/delete-data','ArchiveMovieController@deleteData');

    //Master-Request
    $router->post('master/request/get-all','MasterRequestController@getAll');
    $router->post('master/request/get-single','MasterRequestController@getSingle');
    $router->post('master/request/insert-data','MasterRequestController@insertData');
    $router->post('master/request/update-data','MasterRequestController@updateData');
    $router->post('master/request/delete-data','MasterRequestController@deleteData');
    $router->post('master/request/get-param','MasterRequestController@getParam');
    $router->post('master/request/process-param','MasterRequestController@processParam');
    $router->post('master/request/remove-param','MasterRequestController@RemoveParam');

    //Landing-Request
    $router->post('v1/get-sitemap','V1Controller@getSitemap');
    $router->post('v1/get-about-info','V1Controller@getAboutInfo');
    $router->post('v1/get-about-story','V1Controller@getAboutStory');
    $router->post('v1/get-about-doctrin','V1Controller@getAboutDoctrin');
    $router->post('v1/get-about-logo','V1Controller@getAboutLogo');
    $router->post('v1/get-about-iad','V1Controller@getAboutIad');
    $router->post('v1/get-about-intro','V1Controller@getAboutIntro');
    $router->post('v1/get-about-vision','V1Controller@getAboutVision');
    $router->post('v1/get-about-mision','V1Controller@getAboutMision');
    $router->post('v1/get-about-program','V1Controller@getAboutProgram');
    $router->post('v1/get-about-command','V1Controller@getAboutCommand');

    $router->post('v1/get-integrity-legal','V1Controller@getIntegrityLegal');
    $router->post('v1/get-integrity-mechanism','V1Controller@getIntegrityMechanism');
    $router->post('v1/get-integrity-arrangement','V1Controller@getIntegrityArrangement');
    $router->post('v1/get-integrity-accountability','V1Controller@getIntegrityAccountability');
    $router->post('v1/get-integrity-professionalism','V1Controller@getIntegrityProfessionalism');
    $router->post('v1/get-integrity-innovation','V1Controller@getIntegrityInnovation');
    $router->post('v1/get-integrity-supervision','V1Controller@getIntegritySupervision');
    
    $router->post('v1/get-information-unit','V1Controller@getInformationUnit');
    $router->post('v1/get-information-structural','V1Controller@getInformationStructural');
    $router->post('v1/get-information-infrastructure','V1Controller@getInformationInfrastructure');
    $router->post('v1/get-information-service','V1Controller@getInformationService');
    $router->post('v1/get-information-dpo','V1Controller@getInformationDpo');
    $router->post('v1/get-information-structurals','V1Controller@getInformationStructurals');

    $router->post('v1/get-conference-news','V1Controller@getConferenceNews');
    $router->post('v1/get-conference-announcement','V1Controller@getConferenceAnnouncement');
    $router->post('v1/get-conference-event','V1Controller@getConferenceEvent');

    $router->post('v1/get-archive-regulation','V1Controller@getArchiveRegulation');
    $router->post('v1/get-archive-photo','V1Controller@getArchivePhoto');
    $router->post('v1/get-archive-movie','V1Controller@getArchiveMovie');

    $router->post('v1/get-banner','V1Controller@getBanner');
    $router->post('v1/get-infografis','V1Controller@getInfografis');
    $router->post('v1/get-related','V1Controller@getRelated');
    $router->post('v1/get-medsos','V1Controller@getMedsos');
    $router->post('v1/get-rating','V1Controller@getRating');
    $router->post('v1/get-visitor','V1Controller@getVisitor');

    $router->post('v1/set-rating','V1Controller@setRating');
    $router->post('v1/set-newsletter','V1Controller@setNewsletter');
    $router->post('v1/set-contactus','V1Controller@setContactus');

    $router->get('v1/read-information-unit/{slug}/{id}','V1Controller@readInformationUnit');
    $router->get('v1/read-information-service/{slug}/{id}','V1Controller@readInformationService');
    $router->get('v1/read-information-structural/{slug}/{id}','V1Controller@readInformationStructural');
    $router->get('v1/read-information-infrastructure/{slug}/{id}','V1Controller@readInformationInfrastructure');
    $router->get('v1/read-information-dpo/{slug}/{id}','V1Controller@readInformationDpo');

    $router->get('v1/read-conference-news/{slug}/{id}','V1Controller@readConferenceNews');
    $router->get('v1/read-conference-announcement/{slug}/{id}','V1Controller@readConferenceAnnouncement');
    $router->get('v1/read-conference-event/{slug}/{id}','V1Controller@readConferenceEvent');

    $router->post('v1/get-contactus','V1Controller@getContactus');
    $router->post('v1/get-home','V1Controller@getHome');
    $router->post('v1/get-search','V1Controller@getSearch');
    $router->post('v1/get-conference-news-other','V1Controller@getConferenceNewsOther');
    $router->post('v1/get-conference-news-headline','V1Controller@getConferenceNewsHeadline');
    $router->post('v1/get-conference-news-regional','V1Controller@getConferenceNewsRegional');
    $router->post('v1/menu-navigation','V1Controller@menuNavigation');
    $router->post('v1/menu-access','V1Controller@menuAccess');

    $router->post('chat/get-by-type','ChatController@getByType');
    $router->post('chat/get-by-single','ChatController@getBySingle');
    $router->post('chat/process-data','ChatController@processData');
    $router->post('chat/remove-data','ChatController@removeData');
    $router->post('chat/get-message','ChatController@getMessage');
    $router->post('chat/process-message','ChatController@processMessage');
    $router->post('chat/check-message','ChatController@checkMessage');

    $router->get('v1/reset-user/{id}','V1Controller@resetUser');
});