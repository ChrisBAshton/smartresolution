-- #######################################################################
-- #################################################### Account Types ####
-- #######################################################################

CREATE TABLE IF NOT EXISTS account_details (
    login_id INTEGER PRIMARY KEY NOT NULL,
    email    VARCHAR(140) UNIQUE NOT NULL,
    password VARCHAR(140) NOT NULL,
    verified BOOLEAN DEFAULT true
);

CREATE TABLE IF NOT EXISTS administrators (
    login_id INTEGER,
    FOREIGN KEY(login_id) REFERENCES account_details(login_id)
);

CREATE TABLE IF NOT EXISTS organisations (
    login_id        INTEGER,
    type            VARCHAR(30),
    name            VARCHAR(140),
    description     VARCHAR(1000),
    -- etc
    CHECK (type in ("law_firm", "mediation_centre")),
    FOREIGN KEY(login_id) REFERENCES account_details(login_id)
);

CREATE TABLE IF NOT EXISTS individuals (
    login_id        INTEGER,
    organisation_id INTEGER,
    type            VARCHAR(30),
    surname         VARCHAR(140),
    forename        VARCHAR(140),
    cv              TEXT,
    -- etc
    CHECK (type in ("agent", "mediator")),
    FOREIGN KEY(login_id) REFERENCES account_details(login_id),
    FOREIGN KEY(organisation_id) REFERENCES account_details(login_id)
);

-- #######################################################################
-- #################################################### Disputes #########
-- #######################################################################

CREATE TABLE IF NOT EXISTS disputes (
    dispute_id                INTEGER PRIMARY KEY NOT NULL,
    type                      VARCHAR(100) NOT NULL,
    title                     VARCHAR(140) NOT NULL,
    party_a                   INTEGER NOT NULL,
    party_b                   INTEGER, -- NULL until Dispute has been assigned to Law Firm B
    status                    VARCHAR(40) DEFAULT "ongoing",
    round_table_communication BOOLEAN DEFAULT false,
    CHECK (status in ("ongoing", "resolved", "failed")),
    FOREIGN KEY(dispute_id)   REFERENCES disputes(dispute_id),
    FOREIGN KEY(party_a)      REFERENCES dispute_parties(party_id),
    FOREIGN KEY(party_b)      REFERENCES dispute_parties(party_id)
);

CREATE TABLE IF NOT EXISTS dispute_parties (
    party_id          INTEGER PRIMARY KEY NOT NULL,
    organisation_id   INTEGER NOT NULL,
    individual_id     INTEGER, -- can be NULL until set by Organisation
    summary           VARCHAR(1000),
    FOREIGN KEY(organisation_id) REFERENCES account_details(login_id)
    FOREIGN KEY(individual_id)   REFERENCES account_details(login_id)
);

CREATE TABLE IF NOT EXISTS lifespans (
    lifespan_id INTEGER PRIMARY KEY NOT NULL,
    dispute_id  INTEGER NOT NULL,
    proposer    INTEGER NOT NULL,
    valid_until INTEGER NOT NULL,
    start_time  INTEGER NOT NULL,
    end_time    INTEGER NOT NULL,
    status      VARCHAR(15) DEFAULT "offered",
    CHECK (status in ("offered", "accepted", "declined")),
    FOREIGN KEY(dispute_id) REFERENCES disputes(dispute_id),
    FOREIGN KEY(proposer)   REFERENCES account_details(login_id)
);

-- #######################################################################
-- #################################################### Mediation ########
-- #######################################################################

CREATE TABLE IF NOT EXISTS mediation_offers (
    mediation_offer_id INTEGER PRIMARY KEY NOT NULL,
    dispute_id         INTEGER NOT NULL,
    type               VARCHAR(40) NOT NULL,
    proposer_id        INTEGER NOT NULL, -- id of agent proposing the mediation
    proposed_id        INTEGER NOT NULL, -- id of mediator/mediation centre being proposed
    status             VARCHAR(40) NOT NULL DEFAULT "offered",
    CHECK (type   in ("mediation_centre", "mediator")),
    CHECK (status in ("offered", "accepted", "declined")),
    FOREIGN KEY(dispute_id)  REFERENCES disputes(dispute_id),
    FOREIGN KEY(proposer_id) REFERENCES account_details(login_id),
    FOREIGN KEY(proposed_id) REFERENCES account_details(login_id)
);

CREATE TABLE IF NOT EXISTS mediators_available (
    mediator_id INTEGER NOT NULL,
    dispute_id  INTEGER NOT NULL,
    FOREIGN KEY(mediator_id) REFERENCES account_details(login_id),
    FOREIGN KEY(dispute_id)  REFERENCES disputes(dispute_id)
);

-- #######################################################################
-- #################################################### Miscellaneous ####
-- #######################################################################

CREATE TABLE IF NOT EXISTS messages (
    message_id   INTEGER PRIMARY KEY NOT NULL,
    dispute_id   INTEGER NOT NULL,
    author_id    INTEGER NOT NULL,
    recipient_id INTEGER, -- NULL BY DEFAULT. If NULL, user is announcing message on the street. If not NULL, this is a private message intended only for the recipient.
    message      TEXT    NOT NULL,
    timestamp    INTEGER NOT NULL,
    FOREIGN KEY(dispute_id) REFERENCES disputes(dispute_id),
    FOREIGN KEY(author_id)  REFERENCES account_details(login_id)
);

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INTEGER PRIMARY KEY NOT NULL,
    recipient_id    INTEGER NOT NULL,
    message         VARCHAR(300) NOT NULL,
    url             VARCHAR(300) NOT NULL,
    read            BOOLEAN DEFAULT false,
    FOREIGN KEY(recipient_id) REFERENCES account_details(login_id)
);

CREATE TABLE IF NOT EXISTS evidence (
    evidence_id INTEGER PRIMARY KEY NOT NULL,
    dispute_id  INTEGER NOT NULL,
    uploader_id INTEGER NOT NULL,
    filepath    VARCHAR(300) NOT NULL,
    FOREIGN KEY(dispute_id) REFERENCES disputes(dispute_id),
    FOREIGN KEY(uploader_id) REFERENCES account_details(login_id)
);
