CREATE TABLE [dbo].[groups] (
    [id] INT NOT NULL IDENTITY(1,1),
    [name] VARCHAR(100) NULL,
    [enabled] BIT NULL,
    [created_at] DATETIME NOT NULL CONSTRAINT DF_groups_created_at DEFAULT GETDATE(),
    CONSTRAINT PK_groups PRIMARY KEY CLUSTERED ([id] ASC)
);

INSERT INTO [dbo].[groups]([name],[enabled],[created_at])
VALUES
    ('coders', 1, '2012-02-01 21:17:50'),
    ('jazzman', 0, '2012-02-01 21:18:40');


CREATE TABLE [dbo].[users] (
    [id] INT NOT NULL IDENTITY(1,1),
    [uuid] BINARY(16) NULL,
    [name] VARCHAR(30) NULL,
    [email] VARCHAR(255) NULL,
    [is_active] BIT NOT NULL CONSTRAINT DF_users_is_active DEFAULT 1,
    [created_at] DATETIME NOT NULL CONSTRAINT DF_users_created_at DEFAULT GETDATE(),
    CONSTRAINT PK_users PRIMARY KEY CLUSTERED ([id] ASC)
);

INSERT INTO [dbo].[users]([uuid],[name],[email],[is_active],[created_at])
VALUES
    (0x11edc34b01d972fa9c1d0242ac120006, 'davert', 'davert@mail.ua', 1, '2012-02-01 21:17:04'),
    (null, 'nick', 'nick@mail.ua', 1, '2012-02-01 21:17:15'),
    (null, 'miles', 'miles@davis.com', 1, '2012-02-01 21:17:25'),
    (null, 'bird', 'charlie@parker.com', 0, '2012-02-01 21:17:39');


CREATE TABLE [dbo].[permissions] (
    [id] INT NOT NULL IDENTITY(1,1),
    [user_id] INT NULL,
    [group_id] INT NULL,
    [role] VARCHAR(30) NULL,
    CONSTRAINT PK_permissions PRIMARY KEY CLUSTERED ([id] ASC),
    CONSTRAINT FK_permissions FOREIGN KEY ([group_id]) REFERENCES [dbo].[groups] ([id]) ON DELETE CASCADE,
    CONSTRAINT FK_users FOREIGN KEY ([user_id]) REFERENCES [dbo].[users] ([id]) ON DELETE CASCADE
);

INSERT INTO [dbo].[permissions]([user_id],[group_id],[role])
VALUES
    (1,1,'member'),
    (2,1,'member'),
    (3,2,'member'),
    (4,2,'admin');


CREATE TABLE [dbo].[order] (
    [id] INT NOT NULL IDENTITY(1,1),
    [name] VARCHAR(255) NOT NULL,
    [status] VARCHAR(255) NOT NULL,
    CONSTRAINT PK_order PRIMARY KEY CLUSTERED ([id] ASC)
);

INSERT INTO [dbo].[order]([name],[status]) VALUES ('main', 'open');


CREATE TABLE [dbo].[composite_pk] (
    [group_id] INT NOT NULL,
    [id] INT NOT NULL,
    [status] VARCHAR(255) NOT NULL,
    CONSTRAINT PK_composite_pk PRIMARY KEY CLUSTERED ([group_id] ASC, [id] ASC)
);

CREATE TABLE [dbo].[no_pk] (
    [status] varchar(255) NOT NULL
);

CREATE TABLE [dbo].[empty_table] (
    [id] int NOT NULL IDENTITY(1,1),
    [field] varchar(255),
    CONSTRAINT [PK_empty_table] PRIMARY KEY CLUSTERED ([id])
);
