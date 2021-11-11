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
    paymentIntentId       char(27) not null
        primary key,
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
    eventId         char(28)     not null
        primary key,
    type            varchar(128) not null,
    relatedObjectId varchar(64) null,
    details         json null,
    created         timestamp    not null
);