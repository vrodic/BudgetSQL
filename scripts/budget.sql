CREATE TABLE MainItems(
  id SERIAL,
  code varchar(200),
  parentfine varchar(200),
  name varchar(255),
  parent int,
  parentmid varchar(20),
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

CREATE TABLE Interesting(
    id SERIAL,
    name varchar(255),
    params text UNIQUE,
    clickcnt int DEFAULT '0'
);

INSERT INTO Interesting (name, params) VALUES('Informatizacija','nameq=&typecode=1&parent=-1');
INSERT INTO Interesting (name, params) VALUES('Informatizacija,  Rashodi za nabavu neproizvedene imovine','nameq=&code=41&parent=-1&typecode=1');
INSERT INTO Interesting (name, params) VALUES('Rashodi za nabavu neproizvedene imovine','nameq=&code=41&parent=-1');
INSERT INTO Interesting (name, params) VALUES('Sve šifre koje počinju sa A','nameq=&codeq=A&parent=-1');
INSERT INTO Interesting (name, params) VALUES('Duhovna pomoć','nameq=duhovna+pomoć&typecode=-1&parent=-1');
