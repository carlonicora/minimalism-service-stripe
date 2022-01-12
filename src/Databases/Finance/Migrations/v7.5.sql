create table stripeSubscriptions
(
    subscriptionId       bigint unsigned primary key auto_increment,
    stripeSubscriptionId char(28)     not null,
    stripePriceId        char(30)     not null,
    stripeProductId      bigint unsigned not null,
    payerId              bigint unsigned not null,
    payerEmail           varchar(255) not null,
    reciperId            bigint unsigned not null,
    receiperEmail        varchar(255) not null,
    frequency            enum ('monthly') not null,
    amount               int unsigned not null,
    phlowFeePercent      int unsigned not null,
    currency             char(3)      not null,
    status               varchar(64)  not null,
    createdAt            timestamp    not null,
    updatedAt            timestamp null
);

create unique index stripeSubscriptions_subscriptionId_uindex
    on stripeSubscriptions (subscriptionId);

create unique index stripeSubscriptions_authorId_payerId_uindex
    on stripeSubscriptions (authorId, payerId);

create table stripeCustomers
(
    userId           bigint unsigned primary key,
    stripeCustomerId char(18)     not null,
    email            varchar(255) not null,
    createdAt        timestamp    not null,
    updatedAt        timestamp null
);

create unique index stripeCustomers_userId_uindex
    on stripeCustomers (userId);

create unique index stripeCustomers_stripeCustomerId_uindex
    on stripeCustomers (stripeCustomerId);

alter table stripePaymentIntents
    add receiperEmail varchar(255) not null after receiperAccountId;

