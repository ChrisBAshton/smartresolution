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
    law_firm_a    INTEGER NOT NULL,
    agent_a       INTEGER NOT NULL,
    law_firm_b    INTEGER, -- NULL until Agent A has assigned the Dispute to Law Firm B
    agent_b       INTEGER, -- NULL until an Agent has been assigned by Law Firm B
    lifespan_id   INTEGER, -- NULL until a Lifespan has been negotiated
    resolution_id INTEGER, -- NULL until resolved
    mediation_id  INTEGER, -- NULL until in Mediation
    FOREIGN KEY(law_firm_a) REFERENCES account_details(login_id),
    FOREIGN KEY(agent_a)    REFERENCES account_details(login_id),
    FOREIGN KEY(law_firm_a) REFERENCES account_details(login_id),
    FOREIGN KEY(agent_b)    REFERENCES account_details(login_id)
);