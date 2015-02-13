-- @TODO - read over old SQL report from year 2 and see if I can properly link foreign keys.

-- #######################################################################
-- #################################################### Account Types ####
-- #######################################################################

CREATE TABLE IF NOT EXISTS 'account_details' (
    -- we use SQLite3's built-in "rowid" column rather than explicitly defining an auto-incrementing Primary Key
    'email' VARCHAR(140) NOT NULL,
    'password' VARCHAR(140) NOT NULL
);

CREATE TABLE IF NOT EXISTS 'law_firms' (
    -- rowid
    'login_details_id' INT, -- Foreign Key: account_details.rowid
    'name' VARCHAR(140),
    'description' VARCHAR(1000)
    -- etc
);

CREATE TABLE IF NOT EXISTS 'mediation_centres' (
    -- rowid
    'login_details_id' INT, -- Foreign Key: account_details.rowid
    'name' VARCHAR(140),
    'description' VARCHAR(1000)
    -- etc
);

CREATE TABLE IF NOT EXISTS 'agents' (
    -- rowid
    'login_details_id' INT, -- Foreign Key: account_details.rowid
    'surname' VARCHAR(140),
    'forename' VARCHAR(1000),
    'path_to_cv' VARCHAR(300)
    -- etc
);

CREATE TABLE IF NOT EXISTS 'mediators' (
    -- rowid
    'login_details_id' INT, -- Foreign Key: account_details.rowid
    'surname' VARCHAR(140),
    'forename' VARCHAR(1000),
    'path_to_cv' VARCHAR(300)
    -- etc
);

-- #######################################################################
-- #################################################### Disputes #########
-- #######################################################################

CREATE TABLE IF NOT EXISTS 'disputes' (
    -- rowid
    'company_a' INT,
    'company_b' INT,
    'agent_a'   INT,
    'agent_b'   INT,
    'status'    VARCHAR(30), -- 'preparing', 'in_progress', 'in_mediation', 'closed_successfully', 'closed_unsuccessfully'
    'lifespan'  INT -- Foreign Key
);

-- ONLY ONE "NEGOTIATING" DISPUTE LIFESPAN CAN BE OPEN AT ANY ONE TIME.
CREATE TABLE IF NOT EXISTS 'dispute_lifespans' (
    -- rowid
    'dispute_id' INT, -- foreign key, see 'disputes' table
    'status'     VARCHAR(30), -- 'active', 'negotiating', 'inactive' (can be inactive when Dispute is closed OR when another lifespan is negotiated and takes precedence) @TODO this can all be inferred from the other fields, so could lead to redundancy. Consider removing this.
    'accepted_by_agent_a' BOOLEAN,
    'accepted_by_agent_b' BOOLEAN,
    'start_time' INT, -- UNIX timestamp
    'end_time'   INT, -- UNIX timestamp
    'timestamp'  INT  -- UNIX timestamp of when the Lifespan is finally agreed by both Agents - used in deciding which Lifespan takes precedence.
);

CREATE TABLE IF NOT EXISTS 'chatrooms' (
    -- rowid
    'dispute_id' INT,         -- foreign key
    'status'     VARCHAR(30), -- 'active', 'blocked'
    'type'       VARCHAR(30)  -- 'communication', 'mediation' @TODO maybe this should be a separate table
);

-- This model is good because it allows us to add a third person (i.e. the Mediator) to the chatroom, for round-table communication.
CREATE TABLE IF NOT EXISTS 'chatroom_participants' (
    -- rowid,
    'chatroom_id'    INT, -- foreign key
    'participant_id' INT, -- foreign key relating to account_details.rowid
);

-- @TODO offers

-- #######################################################################
-- #################################################### Mediation ########
-- #######################################################################

-- @TODO choosing a Mediation Centre, Mediator, etc. Maybe I can extract the common "negotiation" and syncrhonisation logic into another table? Since this is repeated for choosing mediation centre, mediator, Dispute lifespan, Dispute Resolution propositions, etc.

