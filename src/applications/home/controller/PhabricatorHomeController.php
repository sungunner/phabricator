<?php

abstract class PhabricatorHomeController extends PhabricatorController {

  public function buildNav() {
    $user = $this->getRequest()->getUser();

    $nav = new AphrontSideNavFilterView();
    $nav->setBaseURI(new PhutilURI('/'));

    $applications = id(new PhabricatorApplicationQuery())
      ->setViewer($user)
      ->withInstalled(true)
      ->withUnlisted(false)
      ->withLaunchable(true)
      ->execute();

    $pinned = $user->getUserSetting(
      PhabricatorPinnedApplicationsSetting::SETTINGKEY);

    // Force "Applications" to appear at the bottom.
    $meta_app = 'PhabricatorApplicationsApplication';
    $pinned = array_fuse($pinned);
    unset($pinned[$meta_app]);
    $pinned[$meta_app] = $meta_app;
    $applications[$meta_app] = PhabricatorApplication::getByClass($meta_app);

    $tiles = array();

    $home_app = new PhabricatorHomeApplication();

    $tiles[] = id(new PhabricatorApplicationLaunchView())
      ->setApplication($home_app)
      ->setApplicationStatus($home_app->loadStatus($user))
      ->addClass('phabricator-application-launch-phone-only')
      ->setUser($user);

    foreach ($pinned as $pinned_application) {
      if (empty($applications[$pinned_application])) {
        continue;
      }

      $application = $applications[$pinned_application];

      $tile = id(new PhabricatorApplicationLaunchView())
        ->setApplication($application)
        ->setApplicationStatus($application->loadStatus($user))
        ->setUser($user);

      $tiles[] = $tile;
    }

    $nav->addCustomBlock(
      phutil_tag(
        'div',
        array(
          'class' => 'application-tile-group',
        ),
        $tiles));

    $nav->addFilter(
      '',
      pht('Customize Menu...'),
      '/settings/panel/home/');

    $nav->addClass('phabricator-side-menu-home');
    $nav->selectFilter(null);

    return $nav;
  }

}
