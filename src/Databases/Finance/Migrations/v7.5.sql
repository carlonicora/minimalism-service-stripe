create table stripeSubscriptions
(
    subscriptionId            bigint unsigned primary key auto_increment,
    stripeSubscriptionId      char(28)         not null,
    stripeLastInvoiceId       char(27)         not null,
    stripeLastPaymentIntentId char(27)         not null,
    stripePriceId             char(30)         not null,
    productId                 bigint unsigned  not null,
    payerId                   bigint unsigned  not null,
    payerEmail                varchar(255)     not null,
    recieperId                bigint unsigned  not null,
    recieperEmail             varchar(255)     not null,
    frequency                 enum ('monthly') not null,
    amount                    int unsigned     not null,
    phlowFeePercent           int unsigned     not null,
    currency                  char(3)          not null,
    status                    varchar(64)      not null,
    createdAt                 timestamp        not null,
    updatedAt                 timestamp        null
);

create unique index stripeSubscriptions_subscriptionId_uindex
    on stripeSubscriptions (subscriptionId);

create unique index stripeSubscriptions_recieperId_payerId_uindex
    on stripeSubscriptions (recieperId, payerId);

create table stripeCustomers
(
    userId           bigint unsigned primary key,
    stripeCustomerId char(18)     not null,
    email            varchar(255) not null,
    createdAt        timestamp    not null,
    updatedAt        timestamp    null
);

create unique index stripeCustomers_userId_uindex
    on stripeCustomers (userId);

create unique index stripeCustomers_stripeCustomerId_uindex
    on stripeCustomers (stripeCustomerId);

create table stripeProducts
(
    productId       bigint unsigned auto_increment,
    stripeProductId char(19)        not null,
    recieperId      bigint unsigned not null,
    name            varchar(255)    not null,
    description     varchar(255)    null,
    createdAt       timestamp       not null,
    updatedAt       timestamp       null,
    constraint stripeProducts_pk
        primary key (productId)
);

create unique index stripeProducts_recieperId_uindex
    on stripeProducts (recieperId);

create unique index stripeProducts_stripeProductId_uindex
    on stripeProducts (stripeProductId);

create table stripeInvoices
(
    invoiceId             bigint unsigned auto_increment,
    stripeInvoiceId       char(27)                                                not null,
    stripeCustomerId      char(18)                                                not null,
    subscriptionId        bigint unsigned                                         null,
    payerId               bigint unsigned                                         not null,
    payerEmail            varchar(255)                                            not null,
    recieperId            bigint unsigned                                         not null,
    recieperEmail         varchar(255)                                            not null,
    frequency             enum ('monthly')                                        not null,
    amount                int unsigned                                            not null,
    phlowFeePercent       tinyint unsigned                                        not null,
    currency              enum ('eur', 'usd', 'bgp')                              not null,
    status                enum ('draft', 'open', 'paid', 'void', 'uncollectible') not null,
    createdAt             datetime                                                not null,
    updatedAt             datetime                                                null,
    constraint stripeInvoices_pk
        primary key (invoiceId)
);

create unique index stripeInvoices_invoiceId_uindex
    on stripeInvoices (invoiceId);

alter table stripePaymentIntents
    change receiperId recieperId bigint unsigned not null;

alter table stripePaymentIntents
    change receiperAccountId recieperAccountId char(21) not null;

alter table stripePaymentIntents
    add recieperEmail varchar(255) not null after recieperAccountId;
