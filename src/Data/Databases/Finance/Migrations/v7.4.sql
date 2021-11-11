create table stripeAccounts
(
    userId           bigint unsigned primary key,
    stripeAccountId  char(21)         not null,
    email            varchar(255)     not null,
    status           varchar(64)      null,
    payoutsEnabled   tinyint(3) unsigned not null,
    error            varchar(255)     null,
    createdAt        timestamp        not null,
    updatedAt        timestamp        null
);

create unique index stripeAccounts_stripeAccountId_uindex
    on stripeAccounts (stripeAccountId);


create table stripePaymentIntents
(
    paymentId             bigint unsigned auto_increment
        primary key,
    stripePaymentIntentId char(27) null,
    payerId               bigint unsigned not null,
    payerEmail            varchar(255) not null,
    receiperId            bigint unsigned not null,
    receiperAccountId     char(21)     not null,
    amount                int unsigned not null,
    phlowFeeAmount        int unsigned not null,
    currency              char(3)      not null,
    status                varchar(64)  not null,
    error                 varchar(255) null,
    createdAt             timestamp    not null,
    updatedAt             timestamp null
);

create table stripeEvents
(
    eventId      char(28)     not null,
    type         varchar(128) not null,
    dataObjectId varchar(64)  null,
    created      timestamp    not null,
    constraint stripeEvents_eventId_uindex
        unique (eventId)
);