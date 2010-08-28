CREATE TABLE MainItems(
  id SERIAL,
  code varchar(200),
  parentfine varchar(200),
  name varchar(255),
  parent int,
  subitem int,
  typecode int,
  amount1 bigint,
  amount2 bigint,
  amount3 bigint
);

CREATE TABLE ItemValuesYears (
  itemid int,
  year date,
  amount int
);

