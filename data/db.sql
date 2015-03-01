-- #######################################################################
-- #################################################### Account Types ####
-- #######################################################################

CREATE TABLE IF NOT EXISTS account_details (
    login_id INTEGER PRIMARY KEY NOT NULL,
    email    VARCHAR(140) NOT NULL,
    password VARCHAR(140) NOT NULL,
    verified BOOLEAN DEFAULT true
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
    path_to_cv      VARCHAR(300),
    -- etc
    CHECK (type in ("agent", "mediator")),
    FOREIGN KEY(login_id) REFERENCES account_details(login_id),
    FOREIGN KEY(organisation_id) REFERENCES account_details(login_id)
);

-- #######################################################################
-- #################################################### Disputes #########
-- #######################################################################

CREATE TABLE IF NOT EXISTS disputes (
    dispute_id    INTEGER PRIMARY KEY NOT NULL,
    type          VARCHAR(100) NOT NULL,
    title         VARCHAR(140) NOT NULL,
    party_a       INTEGER NOT NULL,
    party_b       INTEGER, -- NULL until Dispute has been assigned to Law Firm B
    third_party   INTEGER, -- NULL until in Mediation
    lifespan_id   INTEGER, -- NULL until a Lifespan has been negotiated
    resolution_id INTEGER, -- NULL until resolved
    FOREIGN KEY(party_a)     REFERENCES dispute_parties(party_id),
    FOREIGN KEY(party_b)     REFERENCES dispute_parties(party_id),
    FOREIGN KEY(third_party) REFERENCES dispute_parties(party_id)
);

CREATE TABLE IF NOT EXISTS dispute_parties (
    party_id          INTEGER PRIMARY KEY NOT NULL,
    organisation_id   INTEGER NOT NULL,
    individual_id     INTEGER, -- can be NULL until set by Organisation
    summary           VARCHAR(1000),
    FOREIGN KEY(organisation_id) REFERENCES account_details(login_id)
    FOREIGN KEY(individual_id)   REFERENCES account_details(login_id)
);

-- #######################################################################
-- #################################################### Miscellaneous ####
-- #######################################################################

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INTEGER PRIMARY KEY NOT NULL,
    recipient_id    INTEGER NOT NULL,
    message         VARCHAR(300) NOT NULL,
    url             VARCHAR(300) NOT NULL,
    read            BOOLEAN DEFAULT false,
    FOREIGN KEY(recipient_id) REFERENCES account_details(login_id)
);