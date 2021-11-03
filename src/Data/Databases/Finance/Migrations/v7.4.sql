create table stripeAccounts
(
    accountId        bigint unsigned auto_increment
        primary key,
    userId           bigint unsigned  not null,
    stripeAccountId  char(21)         not null,
    email            varchar(255)     not null,
    connectionStatus tinyint unsigned not null,
    error            varchar(255)     null,
    createdAt        timestamp        not null,
    updatedAt        timestamp        null
);

create table stripePayments
(
    paymentId       bigint unsigned auto_increment
        primary key,
    paymentIntentId char(27)         null,
    payerId         bigint unsigned  not null,
    receiperId      bigint unsigned  not null,
    amount          int unsigned     not null,
    phlowFeeAmount  int unsigned     not null,
    currency        char(3)          not null,
    status          tinyint unsigned not null,
    error           varchar(255)     null,
    createdAt       timestamp        not null,
    updatedAt       timestamp        null
);

