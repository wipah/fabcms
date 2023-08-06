--
-- Create table `fabcms_forum_warns`
--
CREATE TABLE `fabcms_forum_warns`
(
    `ID`                MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `user_ID`           MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `warned_by_user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `warn_resource_ID`  MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `warn_date`         DATE                  NULL DEFAULT NULL,
    `warn_reason`       TEXT                  NULL DEFAULT NULL,
    `warn_type`         SMALLINT(6) UNSIGNED  NULL DEFAULT NULL COMMENT '0 = topic, 1 = post; 3 = PM, 4 = hack',
    INDEX `user_ID` (`user_ID`),
    INDEX `warned_by_user_ID` (`warned_by_user_ID`),
    INDEX `warn_resource_ID` (`warn_resource_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_forum_user_stats`
--
CREATE TABLE `fabcms_forum_user_stats`
(
    `ID`           MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`      MEDIUMINT(8) UNSIGNED NOT NULL,
    `reply_count`  MEDIUMINT(8) UNSIGNED NOT NULL,
    `topic_count`  MEDIUMINT(8) UNSIGNED NOT NULL,
    `latest_post`  DATETIME              NOT NULL,
    `latest_reply` DATETIME              NOT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_user_config`
--
CREATE TABLE `fabcms_forum_user_config`
(
    `ID`               MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`          MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT 0,
    `user_avatar`      VARCHAR(255)          NULL     DEFAULT NULL,
    `user_avatar_type` TINYINT(4) UNSIGNED   NULL     DEFAULT NULL COMMENT ' 0 = internal, 1 = custom',
    `email_notify`     TINYINT(4) UNSIGNED   NOT NULL DEFAULT 1 COMMENT '0 = false (no notifications), 1 = true;',
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_forum_triggers`
--
CREATE TABLE fabcms_forum_triggers
(
    ID           mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    event        varchar(255)        DEFAULT NULL,
    `trigger`    varchar(255)        DEFAULT NULL,
    trigger_data text                DEFAULT NULL,
    enabled      tinyint(4) UNSIGNED DEFAULT 0,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_forum_topics`
--
CREATE TABLE `fabcms_forum_topics`
(
    `ID`                     MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `thread_ID`              MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `user_ID`                MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `latest_reply_user_ID`   MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `moved_topic_ID`         MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `moved_thread_ID`        MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `best_reply_ID`          MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `duplicate_of_topic_ID`  MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `approved_by_user_ID`    MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `topic_trackback`        VARCHAR(255)          NULL DEFAULT NULL,
    `reply_count`            SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `moved_topic_trackback`  VARCHAR(255)          NULL DEFAULT NULL,
    `moved_thread_trackback` VARCHAR(255)          NULL DEFAULT NULL,
    `topic_title`            VARCHAR(255)          NULL DEFAULT NULL,
    `tags`                   VARCHAR(255)          NULL DEFAULT NULL,
    `date_created`           DATETIME              NULL DEFAULT NULL,
    `date_latest_update`     DATETIME              NULL DEFAULT NULL,
    `replies`                SMALLINT(5) UNSIGNED  NULL DEFAULT NULL,
    `topic_message`          TEXT                  NULL DEFAULT NULL,
    `pinned`                 TINYINT(3) UNSIGNED   NULL DEFAULT NULL,
    `approved`               TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `approved_date`          DATE                  NULL DEFAULT NULL,
    `locked`                 TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `IP`                     VARCHAR(15)           NULL DEFAULT NULL,
    `minimum_post_required`  TINYINT(4) UNSIGNED   NULL DEFAULT 0,
    `visible`                TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `thread_ID` (`thread_ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `latest_reply_user_ID` (`latest_reply_user_ID`),
    INDEX `moved_topic_ID` (`moved_topic_ID`),
    INDEX `moved_thread_ID` (`moved_thread_ID`),
    INDEX `best_reply_ID` (`best_reply_ID`),
    INDEX `duplicate_of_topic_ID` (`duplicate_of_topic_ID`),
    INDEX `approved_by_user_ID` (`approved_by_user_ID`),
    FULLTEXT INDEX `topic_message` (`topic_message`),
    FULLTEXT INDEX `topic_title` (`topic_title`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_forum_thread_visibility`
--
CREATE TABLE `fabcms_forum_thread_visibility`
(
    `ID`       MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `thred_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `group_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `status`   TINYINT(4) UNSIGNED   NULL DEFAULT 1 COMMENT '0 = not visible; 1 = visible;',
    INDEX `thred_ID` (`thred_ID`),
    INDEX `group_ID` (`group_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_threads_groups`
--
CREATE TABLE `fabcms_forum_threads_groups`
(
    `ID`       MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `group_ID` (`group_ID`)
)
    COMMENT ='Groups that can control threads'
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_threads`
--
CREATE TABLE `fabcms_forum_threads`
(
    `ID`                          MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_ID`                 MEDIUMINT(9) UNSIGNED NOT NULL,
    `latest_topic_ID`             MEDIUMINT(8) UNSIGNED NOT NULL,
    `latest_topic_user_ID`        SMALLINT(5) UNSIGNED  NOT NULL,
    `is_sub_threads_of_thread_ID` MEDIUMINT(9) UNSIGNED NULL     DEFAULT NULL,
    `lang`                        VARCHAR(2)            NOT NULL,
    `thread_name`                 VARCHAR(255)          NOT NULL,
    `thread_trackback`            VARCHAR(255)          NULL     DEFAULT NULL,
    `thread_description`          TEXT                  NULL     DEFAULT NULL,
    `topics_count`                MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
    `order`                       SMALLINT(5) UNSIGNED  NOT NULL,
    `visible`                     TINYINT(4) UNSIGNED   NOT NULL DEFAULT 0,
    `has_subthreads`              TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `topic_approvation`           TINYINT(4) UNSIGNED   NULL     DEFAULT NULL COMMENT 'Specify if the threads contains topics that requires moderation/approvation',
    PRIMARY KEY (`ID`),
    INDEX `category_ID` (`category_ID`),
    INDEX `latest_topic_ID` (`latest_topic_ID`),
    INDEX `latest_topic_user_ID` (`latest_topic_user_ID`),
    INDEX `is_sub_threads_of_thread_ID` (`is_sub_threads_of_thread_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_subscriptions`
--
CREATE TABLE `fabcms_forum_subscriptions`
(
    `ID`                MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`           MEDIUMINT(9) UNSIGNED NOT NULL,
    `topic_ID`          MEDIUMINT(9) UNSIGNED NOT NULL,
    `latest_check_date` DATETIME              NOT NULL,
    `notify_sent`       TINYINT(3) UNSIGNED   NOT NULL,
    `status`            TINYINT(3) UNSIGNED   NOT NULL DEFAULT 1 COMMENT '0 = disabled, 1 = enabled, 2 = removed;',
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `topic_ID` (`topic_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_signatures`
--
CREATE TABLE `fabcms_forum_signatures`
(
    `ID`                   MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`              MEDIUMINT(9) UNSIGNED NOT NULL,
    `signature`            VARCHAR(2048)         NOT NULL,
    `signature_unapproved` TINYINT(4) UNSIGNED   NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_forum_replies`
--
CREATE TABLE `fabcms_forum_replies`
(
    `ID`             MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`        MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `topic_ID`       MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `date`           DATETIME              NULL DEFAULT NULL,
    `reply`          TEXT                  NULL DEFAULT NULL,
    `IP`             VARCHAR(15)           NULL DEFAULT NULL,
    `is_edited`      TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `is_best_answer` TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `approved`       TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `approved_date`  TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `visible`        TINYINT(3) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `topic_ID` (`topic_ID`),
    FULLTEXT INDEX `reply` (`reply`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_reactions`
--
CREATE TABLE `fabcms_forum_reactions`
(
    `ID`            MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`       MEDIUMINT(8) UNSIGNED NULL DEFAULT NULL,
    `topic_ID`      MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `reply_id`      MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `type`          TINYINT(3) UNSIGNED   NULL DEFAULT NULL COMMENT '1 = topic; 2 = reply',
    `date`          DATETIME              NULL DEFAULT NULL COMMENT '0 = topic; 1 = reply;',
    `reaction_type` TINYINT(4) UNSIGNED   NULL DEFAULT NULL COMMENT '1 = approve; 2 = angry; 3 = love; 4 = wow; 5 = shocked',
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `topic_ID` (`topic_ID`),
    INDEX `reply_id` (`reply_id`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_forum_ranks`
--
CREATE TABLE `fabcms_forum_ranks` (
                                      `ID` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
                                      `name` VARCHAR(255) NOT NULL DEFAULT '0' COLLATE 'utf16_general_ci',
                                      `post_required` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT 0,
                                      `stars` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
                                      `icon` VARCHAR(255) NULL DEFAULT NULL,
                                      PRIMARY KEY (`ID`)
)
    COLLATE='utf8_general_ci'
    ENGINE=Aria;


--
-- Create table `fabcms_forum_parsers`
--
CREATE TABLE fabcms_forum_parsers
(
    ID      mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
    parser  varchar(128)        DEFAULT NULL,
    `order` tinyint(3) UNSIGNED DEFAULT NULL,
    enabled tinyint(4) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = INNODB,
    CHARACTER SET utf8,
    COLLATE utf8_general_ci,
    ROW_FORMAT = COMPACT;

--
-- Create table `fabcms_forum_logs`
--
CREATE TABLE `fabcms_forum_logs`
(
    `ID`      INT(11) UNSIGNED      NOT NULL AUTO_INCREMENT,
    `user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `IDX`     MEDIUMINT(9) UNSIGNED NULL,
    `event`   VARCHAR(255)          NULL,
    `log`     TEXT                  NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


CREATE TABLE `fabcms_forum_groups_users`
(
    `ID`         MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_ID`   MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `user_ID`    MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `note`       TEXT                  NULL DEFAULT NULL,
    `added_date` DATE                  NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `group_ID` (`group_ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


CREATE TABLE `fabcms_forum_groups_permissions`
(
    `ID`                       MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_ID`                 MEDIUMINT(9) UNSIGNED NULL,
    `can_update_config`        TINYINT(4) UNSIGNED   NULL,
    `can_warn_users`           TINYINT(4) UNSIGNED   NULL,
    `can_ban_users`            TINYINT(4) UNSIGNED   NULL,
    `can_edit_replies`         TINYINT(4) UNSIGNED   NULL,
    `can_delete_replies`       TINYINT(4) UNSIGNED   NULL,
    `can_hide_replies`         TINYINT(4) UNSIGNED   NULL,
    `can_post_replies`         TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_delete_own_replies`   TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_edit_own_replies`     TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_edit_topics`          TINYINT(4) UNSIGNED   NULL,
    `can_lock_topics`          TINYINT(4) UNSIGNED   NULL,
    `can_hide_topics`          TINYINT(4) UNSIGNED   NULL,
    `can_delete_topics`        TINYINT(4) UNSIGNED   NULL,
    `can_approve_topics`       TINYINT(4) UNSIGNED   NULL,
    `can_stick_topics`         TINYINT(4) UNSIGNED   NULL,
    `can_delete_own_topics`    TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_view_other_topics`    TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_edit_own_topics`      TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_set_topic_importance` TINYINT(4) UNSIGNED   NULL,
    `can_merge_topics`         TINYINT(4) UNSIGNED   NULL,
    `can_post_topics`          TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_unapprove_signatures` TINYINT(4) UNSIGNED   NULL,
    `can_edit_signatures`      TINYINT(4) UNSIGNED   NULL,
    `can_move_topics`          TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_manage_forums`        TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_manage_categories`    TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `can_manage_threads`       TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `group_ID` (`group_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

--
-- Create table `fabcms_forum_groups`
--
CREATE TABLE fabcms_forum_groups
(
    ID                  mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    group_name          varchar(255)        DEFAULT NULL,
    group_description   text                DEFAULT NULL,
    is_guest            tinyint(4) UNSIGNED DEFAULT NULL,
    is_registered       tinyint(4) UNSIGNED DEFAULT NULL,
    is_moderator        tinyint(4) UNSIGNED DEFAULT NULL,
    is_global_moderator tinyint(4) UNSIGNED DEFAULT NULL,
    is_admin            tinyint(4) UNSIGNED DEFAULT NULL,
    group_html_color    varchar(6)          DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_forum_forums`
--
CREATE TABLE `fabcms_forum_forums`
(
    `ID`                    MEDIUMINT(9) UNSIGNED  NOT NULL AUTO_INCREMENT,
    `category_ID`           MEDIUMINT(9) UNSIGNED  NOT NULL,
    `latest_thread_user_ID` MEDIUMINT(11) UNSIGNED NOT NULL,
    `latest_thread_ID`      MEDIUMINT(8) UNSIGNED  NOT NULL,
    `lang`                  VARCHAR(2)             NOT NULL,
    `forum_name`            VARCHAR(255)           NOT NULL,
    `forum_trackback`       VARCHAR(255)           NULL     DEFAULT NULL,
    `forum_description`     TEXT                   NULL     DEFAULT NULL,
    `threads_count`         MEDIUMINT(8) UNSIGNED  NOT NULL DEFAULT 0,
    `forum_password`        VARCHAR(255)           NULL     DEFAULT NULL,
    `order`                 SMALLINT(5) UNSIGNED   NOT NULL,
    `visible`               TINYINT(4) UNSIGNED    NOT NULL DEFAULT 0,
    PRIMARY KEY (`ID`),
    INDEX `category_ID` (`category_ID`),
    INDEX `latest_thread_user_ID` (`latest_thread_user_ID`),
    INDEX `latest_thread_ID` (`latest_thread_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
    AVG_ROW_LENGTH = 2730
;


--
-- Create table `fabcms_forum_edits`
--
CREATE TABLE `fabcms_forum_edits`
(
    `ID`                        MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `reply_user_ID`             MEDIUMINT(9) UNSIGNED NOT NULL DEFAULT 0,
    `type`                      TINYINT(4) UNSIGNED   NULL     DEFAULT NULL COMMENT '0 = topic; 1 = post',
    `reason`                    VARCHAR(255)          NOT NULL,
    `reply_approved`            TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `reply_approved_by_user_ID` TINYINT(4) UNSIGNED   NULL     DEFAULT NULL,
    `reply_approved_date`       DATETIME              NULL     DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `reply_user_ID` (`reply_user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_config`
--
CREATE TABLE fabcms_forum_config
(
    ID              mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    param           varchar(255)          NOT NULL,
    value           varchar(2056)         NOT NULL,
    extended_valute text DEFAULT NULL,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_forum_category_visibility`
--
CREATE TABLE `fabcms_forum_category_visibility`
(
    `ID`          MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `group_ID`    MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `status`      MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL COMMENT '0 = not visible; 1 = visible;',
    PRIMARY KEY (`ID`),
    INDEX `category_ID` (`category_ID`),
    INDEX `group_ID` (`group_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;


--
-- Create table `fabcms_forum_categories`
--
CREATE TABLE fabcms_forum_categories
(
    ID            mediumint(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    category_name varchar(255)          NOT NULL DEFAULT '0',
    language      varchar(2)            NOT NULL DEFAULT '0',
    `order`       smallint(5) UNSIGNED           DEFAULT NULL,
    enabled       tinyint(4)            NOT NULL DEFAULT 0,
    PRIMARY KEY (ID)
)
    ENGINE = ARIA,
    AVG_ROW_LENGTH = 4096,
    CHARACTER SET utf8,
    TABLE_CHECKSUM = 0,
    PAGE_CHECKSUM = 1,
    COLLATE utf8_general_ci;

--
-- Create table `fabcms_forum_bans`
--
CREATE TABLE `fabcms_forum_bans`
(
    `ID`                MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`           MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `banned_by_user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `ban_reason`        VARCHAR(1024)         NULL DEFAULT NULL,
    `ban_note`          TEXT                  NULL DEFAULT NULL,
    `ban_start_date`    DATETIME              NULL DEFAULT NULL,
    `ban_end_date`      DATETIME              NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `banned_by_user_ID` (`banned_by_user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria;


CREATE TABLE `fabcms_forum_pm`
(
    `ID`                           TINYINT(4) UNSIGNED   NOT NULL AUTO_INCREMENT,
    `user_ID`                      MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `user_destination_ID`          MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `moderation_requested_user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `moderation_approved_user_ID`  MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `start_date`                   DATETIME              NULL DEFAULT NULL,
    `last_message_date`            DATETIME              NULL DEFAULT NULL,
    `moderation_request_date`      DATETIME              NULL DEFAULT NULL,
    `moderation_requested`         TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `moderation_approved`          TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `is_bot`                       TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `is_unread`                    TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `visible`                      TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `user_ID` (`user_ID`),
    INDEX `user_destination_ID` (`user_destination_ID`),
    INDEX `moderation_requested_user_ID` (`moderation_requested_user_ID`),
    INDEX `moderation_approved_user_ID` (`moderation_approved_user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;

CREATE TABLE `fabcms_forum_pm_messages`
(
    `ID`                             MEDIUMINT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_ID`                        MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `pm_ID`                          MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `moderation_requested_user_ID`   MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `moderation_approved_by_user_ID` MEDIUMINT(9) UNSIGNED NULL DEFAULT NULL,
    `message`                        TEXT                  NULL DEFAULT NULL,
    `message_date`                   DATETIME              NULL DEFAULT NULL,
    `moderation_request_date`        DATETIME              NULL DEFAULT NULL,
    `moderation_requested`           TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `moderation_approved`            TINYINT(4) UNSIGNED   NULL DEFAULT NULL,
    `is_bot`                         TINYINT(4)            NULL DEFAULT NULL,
    PRIMARY KEY (`ID`),
    INDEX `pm_ID` (`pm_ID`),
    INDEX `moderation_requested_user_ID` (`moderation_requested_user_ID`),
    INDEX `moderation_approved_by_user_ID` (`moderation_approved_by_user_ID`),
    INDEX `user_ID` (`user_ID`)
)
    COLLATE = 'utf8_general_ci'
    ENGINE = Aria
;
