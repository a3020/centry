<?php

defined('C5_EXECUTE') or die('Access Denied.');

use Concrete\Core\Support\Facade\Url;

/** @var $form \Concrete\Core\Form\Service\Form */
/** @var $token \Concrete\Core\Validation\CSRF\Token */
/** @var $config \Concrete\Core\Config\Repository\Repository */
/** @var $shouldShowSubscribeButton bool */
/** @var $linkToCentryPortal string */
/** @var $showJobScheduleSection bool */
/** @var $endpoint string */
/** @var $job \Concrete\Package\Centry\Job\Centry */
/** @var $apiMethods array */
?>

<div class="ccm-dashboard-header-buttons ">
    <a
        title="<?php echo t('Open Centry Portal in a new tab.'); ?>"
        href="<?php echo $linkToCentryPortal ?>"
        target="_blank"
        class="btn btn-default">
        <?php echo t('Open Centry Portal') ?>
        <i class="fa fa-external-link"></i>
    </a>

    <?php
    if ($shouldShowSubscribeButton) {
        ?>
        <a
            title="<?php echo t('This will subscribe the domain(s) in Centry.'); ?>"
            href="<?php echo $job->getJobUrl() ?>"
            class="btn btn-success btn-register">
            <?php echo t('Sync with portal') ?>&nbsp;
            <i class="fa fa-refresh"></i>
        </a>
        <?php
    }
    ?>
</div>

<div class="ccm-dashboard-content-inner">
    <form method="post" action="<?php echo $this->action('save'); ?>">
        <?php
        echo $token->output('a3020.centry.settings');
        ?>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t("Centry will communicate with this endpoint. The exact URL can be found on the Centry settings page."); ?>"
                   for="auth_token">
                <?php echo t('Endpoint'); ?>
            </label>
            <div class="input-group">
                <?php
                echo $form->text('endpoint', $endpoint, [
                    'minlength' => 5,
                    'required' => 'required',
                ]);
                ?>
                <span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label launch-tooltip"
                   title="<?php echo t("The registration token can be found on the Centry settings page."); ?>"
                   for="auth_token">
                <?php echo t('Registration token'); ?>
            </label>
            <div class="input-group">
                <?php
                echo $form->text('registration_token', (string) $config->get('centry.registration_token'), [
                    'required' => 'required',
                ]);
                ?>
                <span class="input-group-addon"><i class="fa fa-asterisk"></i></span>
            </div>
        </div>

        <div class="clearfix">
            <div class="pull-right">
                <a href="#" class="toggle-advanced-settings">
                    <div class="caption show-caption">
                        <?php echo t('Show advanced settings'); ?> <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="caption hide-caption hide">
                        <?php echo t('Hide advanced settings'); ?> <i class="fa fa-angle-up"></i>
                    </div>
                </a>
            </div>
        </div>

        <div class="advanced-settings hide">
            <div class="form-group">
                <div>
                    <label class="control-label launch-tooltip"
                           title="<?php echo t("If disabled, Centry won't do anything and the API will become inactive."); ?>"
                           for="enabled">
                        <?php
                        echo $form->checkbox('enabled', 1, (bool) $config->get('centry.enabled'));
                        ?>
                        <?php echo t('Enable Centry'); ?>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label launch-tooltip"
                       title="<?php echo t("Each unique domain will automatically be added to this config setting. " .
                           "You can delete 'old' domains if needed. For each domain a record will be created in Centry."); ?>"
                       for="domains">
                    <?php echo t('Domains'); ?>
                </label>
                <?php
                echo $form->textarea('domains', implode("\n", $config->get('centry.domains')), [
                    'placeholder' => t('Domains will be populated automatically.'),
                ]);
                ?>
            </div>

            <div class="form-group">
                <label class="control-label launch-tooltip"
                   title="<?php echo t("In case you want to limit what data external applications can request, " .
                       "you can deselect one or more options below."); ?>">
                    <?php echo t('External application access'); ?>
                </label>

                <div style="column-count: 2;">
                    <?php
                    foreach ($apiMethods as $apiMethodHandle => $apiMethodName) {
                        ?>
                        <div class="input-group">
                            <label>
                                <?php
                                echo $form->checkbox('api_access_'.$apiMethodHandle, 1, (bool) $config->get('centry.api.methods.'.$apiMethodHandle, true));
                                ?>
                                <?php echo $apiMethodName; ?>
                            </label>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <a href="<?php echo Url::to('/index.php/dashboard/system'); ?>" class="btn btn-default pull-left">
                    <?php echo t('Cancel'); ?>
                </a>
                <?php
                echo $form->submit('submit', t('Save settings'), [
                    'class' => 'btn-primary pull-right'
                ]); ?>
            </div>
        </div>
    </form>

    <?php
    if ($shouldShowSubscribeButton) {
        ?>
        <script>
            $('.btn-register').click(function(e) {
                e.preventDefault();

                var that = this;
                $(that).addClass('disabled');

                $.ajax({
                    url: $(that).attr('href'),
                    data: [
                        {
                            'name': 'auth',
                            'value': '<?php echo $job->generateAuth() ?>'
                        },
                        {
                            'name': 'jID',
                            'value': <?php echo $job->getJobID() ?>
                        }
                    ],
                    dataType: 'json',
                    cache: false
                }).done(function(data) {
                    if (data.error) {
                        alert(data.result);
                    } else {
                        document.location.href = '<?php echo $this->action('subscribed'); ?>';
                    }
                }).fail(function() {
                    alert('<?php echo t('Something went wrong. Please check the Log.'); ?>');
                }).always(function() {
                    $(that).removeClass('disabled');
                });
            });
        </script>
        <?php
    }
    ?>

    <script>
        $('.toggle-advanced-settings').click(function() {
            $('.advanced-settings').toggleClass('hide');
            $(this).find('.caption').toggleClass('hide');
        });
    </script>
</div>
