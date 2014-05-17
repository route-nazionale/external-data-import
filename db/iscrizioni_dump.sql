CREATE TABLE ragazzi(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	codicecensimento VARCHAR(7) NOT NULL,
	nome VARCHAR(100) NOT NULL,
	cognome VARCHAR(100) NOT NULL,
	eta INT NOT NULL,
	idgruppo CHAR(5) NOT NULL,
	handicap TINYINT(1) not null default 0,
	novizio TINYINT(1) not null default 0,
	stradadicoraggio1 TINYINT(1) not null default 0,
	stradadicoraggio2 TINYINT(1) not null default 0,
	stradadicoraggio3 TINYINT(1) not null default 0,
	stradadicoraggio4 TINYINT(1) not null default 0,
	stradadicoraggio5 TINYINT(1) not null default 0
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE gruppi(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL,
	sottocampo INT NOT NULL,
	idgruppo CHAR(5) NOT NULL,
	gemellaggio INT NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE logistica(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sottocampo INT NOT NULL,
	gemellaggio INT NOT NULL,
	totale INT NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE laboratori (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	nome VARCHAR(100) NOT NULL,
	sottocampo INT NOT NULL,
	maxpartecipanti INT NOT NULL,
	minpartecipanti INT NOT NULL,
	organizzatore VARCHAR(100) NOT NULL,
	novizio TINYINT(1) not null default 1,
	handicap TINYINT(1) not null default 1,
	etamassima INT NOT NULL,
	etaminima INT NOT NULL,
	stradadicoraggio1 TINYINT(1) not null default 0,
	stradadicoraggio2 TINYINT(1) not null default 0,
	stradadicoraggio3 TINYINT(1) not null default 0,
	stradadicoraggio4 TINYINT(1) not null default 0,
	stradadicoraggio5 TINYINT(1) not null default 0
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE tavolerotonde (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  sottocampo INT NOT NULL,
  maxpartecipanti INT NOT NULL,
  minpartecipanti INT NOT NULL,
  organizzatore VARCHAR(100) NOT NULL,
  turno1 TINYINT(1) not null default 0,
  turno2 TINYINT(1) not null default 0,
  turno3 TINYINT(1) not null default 0,
  stradadicoraggio1 TINYINT(1) not null default 0,
  stradadicoraggio2 TINYINT(1) not null default 0,
  stradadicoraggio3 TINYINT(1) not null default 0,
  stradadicoraggio4 TINYINT(1) not null default 0,
  stradadicoraggio5 TINYINT(1) not null default 0
)ENGINE=InnoDB DEFAULT CHARSET=utf8;