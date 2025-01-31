<?php

/**
 * @var View $this
 * @var Module[] $modules
 * @var EventList $events
 */

use notify_events\helpers\Html;
use notify_events\models\Event;
use notify_events\models\EventList;
use notify_events\models\Module;
use notify_events\models\View;

?>

<style>
    .column-enabled {
        width: 10%;
    }
    .column-channels,
    .column-priority {
        width: 15%;
    }
    .module-list h3 {
        margin-bottom: 8px;
        border-bottom: 1px solid #23282d;
    }
    .group-list h4 {
        margin: 2px 0;
    }
    .event-list {
        display: flex;
        flex-wrap: wrap;
    }
    .event-list .event-list-item {
        width: 33.33%;
        padding: 2px;
        box-sizing: border-box;
    }
    .event-list .event-list-item .button {
        width: 100%;
    }
    .contact_us {
        margin-top: 20px;
    }
</style>

<div id="notify-events-events">

    <h2><?= __('Event list', WPNE) ?></h2>

    <?php $events->display() ?>

    <div id="wpne-event-create" class="wpne-modal-form" data-title="<?= esc_attr(__('Select event type', WPNE)) ?>" data-width="650" data-height="400">
        <div class="module-list">
            <?php foreach ($modules as $module) { ?>
                <div>
                    <h3><?= $module::module_title() ?></h3>

                    <div class="group-list">
                        <?php foreach ($module->event_list() as $group_title => $events) { /** @var Event $event */ ?>
                            <div>
                                <h4><?= esc_html($group_title) ?></h4>

                                <div class="event-list">
                                    <?php foreach ($events as $event) { ?>
                                        <div class="event-list-item">
                                            <?= Html::a($event::event_title(), [
                                                'controller' => 'event',
                                                'action'     => 'create',
                                                'event'      => rawurlencode($event),
                                            ], [
                                                'class' => 'button',
                                            ]) ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            <div class="contact_us">
                <?= __('Can\'t find some event type or integration? Please <a href="https://notify.events/en/contacts" target="_blank">contact us</a>.', WPNE) ?>
            </div>
        </div>
    </div>

</div>
