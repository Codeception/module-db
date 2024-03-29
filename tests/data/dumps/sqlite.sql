DROP TABLE IF EXISTS "groups";
CREATE TABLE "groups" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "name" VARCHAR, "enabled" BOOLEAN, "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP);
INSERT INTO "groups" VALUES(1,'coders',1,'2012-02-01 21:17:50');
INSERT INTO "groups" VALUES(2,'jazzman',0,'2012-02-01 21:18:40');

DROP TABLE IF EXISTS "permissions";
CREATE TABLE "permissions" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "user_id" INTEGER, "group_id" INTEGER, "role" VARCHAR);
INSERT INTO "permissions" VALUES(1,1,1,'member');
INSERT INTO "permissions" VALUES(2,2,1,'member');
INSERT INTO "permissions" VALUES(5,3,2,'member');
INSERT INTO "permissions" VALUES(7,4,2,'admin');

DROP TABLE IF EXISTS "users";
CREATE TABLE "users" ("name" VARCHAR, "uuid" BLOB DEFAULT NULL, "email" VARCHAR, "created_at" DATETIME DEFAULT CURRENT_TIMESTAMP);
INSERT INTO "users" VALUES('davert',X'11edc34b01d972fa9c1d0242ac120006','davert@mail.ua','2012-02-01 21:17:04');
INSERT INTO "users" VALUES('nick',null,'nick@mail.ua','2012-02-01 21:17:15');
INSERT INTO "users" VALUES('miles',null,'miles@davis.com','2012-02-01 21:17:25');
INSERT INTO "users" VALUES('bird',null,'charlie@parker.com','2012-02-01 21:17:39');

DROP TABLE IF EXISTS "empty_table";
CREATE TABLE "empty_table" ("id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , "field" VARCHAR);

DROP TABLE IF EXISTS "composite_pk";
CREATE TABLE "composite_pk" (
  "group_id" INTEGER NOT NULL,
  "id" INTEGER NOT NULL,
  "status" VARCHAR NOT NULL,
  PRIMARY KEY ("group_id", "id")
) WITHOUT ROWID;

DROP TABLE IF EXISTS "no_pk";
CREATE TABLE "no_pk" (
  "status" VARCHAR NOT NULL
);

DROP TABLE IF EXISTS "order";
CREATE TABLE "order" (
  "id" INTEGER NOT NULL PRIMARY KEY,
  "name" VARCHAR NOT NULL,
  "status" VARCHAR NOT NULL
) WITHOUT ROWID;

insert  into "order"("id","name","status") values (1,'main', 'open');
