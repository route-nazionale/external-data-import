# Dump of table periodopartecipazione
# ------------------------------------------------------------

DROP TABLE IF EXISTS periodopartecipazione;

CREATE TABLE periodopartecipazione (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  kkey int(11) NOT NULL,
  description varchar(50) NOT NULL,
  ruolo enum('ONE','EXTRA','LAB') NOT NULL DEFAULT 'LAB',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table quartiere
# ------------------------------------------------------------

DROP TABLE IF EXISTS quartiere;

CREATE TABLE quartiere (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  quartiere tinyint(3) unsigned NOT NULL,
  route int(11) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table decalimenti
# ------------------------------------------------------------

DROP TABLE IF EXISTS decalimenti;

CREATE TABLE decalimenti (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  kkey int(11) NOT NULL,
  value varchar(50) NOT NULL COMMENT 'nessuno/vegetariano/vegano',
  PRIMARY KEY (id),
  KEY index_key (kkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table deccolazioni
# ------------------------------------------------------------

DROP TABLE IF EXISTS deccolazioni;

CREATE TABLE deccolazioni (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  kkey int(11) NOT NULL,
  value varchar(25) NOT NULL COMMENT 'te/latte/altro',
  PRIMARY KEY (id),
  KEY key_index (kkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table decruolo
# ------------------------------------------------------------

DROP TABLE IF EXISTS decruolo;

CREATE TABLE decruolo (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  kkey int(11) NOT NULL,
  value varchar(25) NOT NULL COMMENT 'ruolo associativo agesci',
  PRIMARY KEY (id),
  KEY key_index (kkey)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table modpagamento
# ------------------------------------------------------------

DROP TABLE IF EXISTS modpagamento;

CREATE TABLE modpagamento (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  kkey int(11) NOT NULL,
  description varchar(25) NOT NULL  ,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table gruppi
# ------------------------------------------------------------

DROP TABLE IF EXISTS gruppi;

CREATE TABLE gruppi (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  idgruppo varchar(255) NOT NULL,
  nome varchar(255) NOT NULL,
  unita varchar(255) NOT NULL,
  regione varchar(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table capo
# ------------------------------------------------------------

CREATE TABLE capo (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  codicecensimento int(11) unsigned NOT NULL COMMENT 'codice censimento agesci',
  nome varchar(255) NOT NULL,
  cognome varchar(255) NOT NULL,
  idgruppo varchar(255) NOT NULL COMMENT 'codice ASA del gruppo',
  idunitagruppo varchar(255) NOT NULL COMMENT 'codice ASA del unita',
  datanascita date NOT NULL,
  eta tinyint(3) unsigned NOT NULL COMMENT 'eta desunta dalla data di nascita',
  sesso varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  cellulare varchar(255) NOT NULL,
  abitazione varchar(255) NOT NULL,
  indirizzo varchar(255) NOT NULL,
  cap varchar(255) NOT NULL,
  citta varchar(255) NOT NULL,
  provincia varchar(255) NOT NULL,
  ruolo tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella decruolo',
  colazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella deccolazioni',
  alimentari tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella decalimenti',
  intolleranzealimentari varchar(255) DEFAULT NULL,
  allergiealimentari text COMMENT 'allergie alimentari',
  allergiefarmaci text COMMENT 'allergie farmaci',
  sensoriali tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  psichiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  lis tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  fisiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  patologie text COMMENT 'patologie',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table capoextra
# ------------------------------------------------------------

DROP TABLE IF EXISTS capoextra;

CREATE TABLE capoextra (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  codicecensimento int(11) unsigned NOT NULL COMMENT 'codice censimento agesci',
  nome varchar(255) NOT NULL,
  cognome varchar(255) NOT NULL,
  datanascita date NOT NULL,
  eta tinyint(3) unsigned NOT NULL COMMENT 'eta desunta dalla data di nascita',
  sesso varchar(255) NOT NULL,
  periodopartecipazione tinyint(1) unsigned NOT NULL COMMENT 'vedi tabella periodopartecipazione',
  pagato tinyint(3) unsigned NOT NULL,
  modpagamento tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella modpagamento',
  colazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella deccolazioni',
  alimentari tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella decalimenti',
  intolleranzealimentari varchar(255) DEFAULT NULL,
  allergiealimentari text COMMENT 'allergie alimentari',
  allergiefarmaci text COMMENT 'allergie farmaci',
  sensoriali tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  psichiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  lis tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  fisiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  patologie text COMMENT 'patologie',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table interni
# ------------------------------------------------------------

DROP TABLE IF EXISTS interni;

CREATE TABLE interni (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  quota tinyint(3) unsigned DEFAULT NULL,
  codicesocio varchar(255) DEFAULT NULL COMMENT 'codice censimento agesci',
  cognome varchar(255) DEFAULT NULL,
  nome varchar(255) DEFAULT NULL,
  sesso varchar(255) DEFAULT NULL,
  luogonascita varchar(255) DEFAULT NULL,
  datanascita int(11) unsigned DEFAULT NULL,
  eta tinyint(3) unsigned DEFAULT NULL,
  indirizzo varchar(255) DEFAULT NULL,
  cap int(11) unsigned DEFAULT NULL,
  residenza varchar(255) DEFAULT NULL,
  prov varchar(255) DEFAULT NULL,
  tel varchar(255) DEFAULT NULL,
  cell varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  email2 varchar(255) DEFAULT NULL,
  proponente varchar(255) DEFAULT NULL,
  stradacoraggio varchar(255) DEFAULT NULL,
  laboratorio varchar(255) DEFAULT NULL,
  obiettivolab text,
  orgoutputfin text,
  fasciaeta varchar(255) DEFAULT NULL,
  materiali text,
  spedizionemateriali varchar(255) DEFAULT NULL,
  esigenze text,
  pernotto varchar(255) DEFAULT NULL,
  arrivo varchar(255) DEFAULT NULL,
  codicesocioaltroanim varchar(255) DEFAULT NULL,
  nomealtroanim text,
  emailaltroanim varchar(255) DEFAULT NULL,
  telefonoaltroanim varchar(255) DEFAULT NULL,
  pernottoaltroanim varchar(255) DEFAULT NULL,
  arrivoaltroanim varchar(255) DEFAULT NULL,
  dataprotocolloaltroanim double DEFAULT NULL,
  nomegruppo varchar(255) DEFAULT NULL,
  nomezona varchar(255) DEFAULT NULL,
  nomereg varchar(255) DEFAULT NULL,
  alimentazione varchar(255) DEFAULT NULL,
  colazione tinyint(1) unsigned DEFAULT NULL,
  note varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table esterni
# ------------------------------------------------------------

DROP TABLE IF EXISTS esterni;

CREATE TABLE esterni (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  cronologia varchar(255) DEFAULT NULL,
  regione varchar(255) DEFAULT NULL,
  aec varchar(255) DEFAULT NULL,
  stradacoraggio varchar(255) DEFAULT NULL,
  titolo varchar(255) DEFAULT NULL,
  obiettivo text COLLATE utf8_unicode_ci,
  info text COLLATE utf8_unicode_ci,
  limiti varchar(255) DEFAULT NULL,
  materiali varchar(255) DEFAULT NULL,
  esigenze text COLLATE utf8_unicode_ci,
  quota tinyint(1) unsigned DEFAULT NULL,
  codicesocio varchar(255) DEFAULT NULL,
  nome varchar(255) DEFAULT NULL,
  cognome varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  telefono varchar(255) DEFAULT NULL,
  pernotto varchar(255) DEFAULT NULL,
  dlgs196 varchar(255) DEFAULT NULL,
  altroanim varchar(255) DEFAULT NULL,
  codicesocioaltroanim varchar(255) DEFAULT NULL,
  nomealtroanim varchar(255) DEFAULT NULL,
  cognomealtroanim varchar(255) DEFAULT NULL,
  emailaltroanim varchar(255) DEFAULT NULL,
  telefonoaltroanim varchar(255) DEFAULT NULL,
  pernottoaltroanim varchar(255) DEFAULT NULL,
  dlgs196altroanim varchar(255) DEFAULT NULL,
  note varchar(255) DEFAULT NULL,
  alloggio varchar(255) DEFAULT NULL,
  colazione tinyint(1) unsigned DEFAULT NULL,
  note2 varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table ragazzo
# ------------------------------------------------------------

DROP TABLE IF EXISTS ragazzo;

CREATE TABLE ragazzo (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  codicecensimento int(11) unsigned NOT NULL,
  nome varchar(255) NOT NULL,
  cognome varchar(255) NOT NULL,
  sesso varchar(255) NOT NULL,
  datanascita date NOT NULL,
  eta tinyint(3) unsigned NOT NULL,
  idgruppo varchar(255) NOT NULL,
  idunitagruppo varchar(255) NOT NULL,
  novizio tinyint(3) unsigned NOT NULL,
  stradadicoraggio1 tinyint(3) unsigned NOT NULL,
  stradadicoraggio2 tinyint(3) unsigned NOT NULL,
  stradadicoraggio3 tinyint(3) unsigned NOT NULL,
  stradadicoraggio4 tinyint(3) unsigned NOT NULL,
  stradadicoraggio5 tinyint(3) unsigned NOT NULL,
  colazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella deccolazioni',
  alimentari tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella decalimenti',
  intolleranzealimentari varchar(255) DEFAULT NULL,
  allergiealimentari text COMMENT 'allergie alimentari',
  allergiefarmaci text COMMENT 'allergie farmaci',
  sensoriali tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  psichiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  lis tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  fisiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  patologie text COMMENT 'patologie',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table oneteam
# ------------------------------------------------------------

DROP TABLE IF EXISTS oneteam;

CREATE TABLE oneteam (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  codicecensimento int(11) unsigned NOT NULL,
  nome varchar(255) NOT NULL,
  cognome varchar(255) NOT NULL,
  datanascita date NOT NULL,
  eta tinyint(3) unsigned NOT NULL,
  sesso varchar(255) NOT NULL,
  periodopartecipazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella periodopartecipazione',
  pagato tinyint(3) unsigned NOT NULL,
  modpagamento tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella modpagamento',
  colazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella deccolazioni',
  alimentari tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella decalimenti',
  intolleranzealimentari varchar(255) DEFAULT NULL,
  allergiealimentari text COMMENT 'allergie alimentari',
  allergiefarmaci text COMMENT 'allergie farmaci',
  sensoriali tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  psichiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  lis tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  fisiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  patologie text COMMENT 'patologie',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table gemellaggio
# ------------------------------------------------------------

DROP TABLE IF EXISTS gemellaggio;

CREATE TABLE gemellaggio (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  area varchar(255) DEFAULT NULL,
  route int(11) unsigned DEFAULT NULL,
  codicesocio varchar(255) DEFAULT NULL,
  regione varchar(255) DEFAULT NULL,
  gruppo varchar(255) DEFAULT NULL,
  idgruppo int(11) unsigned DEFAULT NULL,
  idunita varchar(255) DEFAULT NULL,
  cognome varchar(255) DEFAULT NULL,
  nome varchar(255) DEFAULT NULL,
  email varchar(255) DEFAULT NULL,
  telefono varchar(255) DEFAULT NULL,
  cell double DEFAULT NULL,
  gemellato varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Dump of table capolaboratorio
# ------------------------------------------------------------

DROP TABLE IF EXISTS capolaboratorio;

CREATE TABLE capolaboratorio (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  codicecensimento int(11) unsigned NOT NULL,
  nome varchar(255) NOT NULL,
  cognome varchar(255) NOT NULL,
  datanascita date NOT NULL,
  eta tinyint(3) unsigned NOT NULL,
  sesso varchar(255) NOT NULL,
  periodopartecipazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella periodopartecipazione',
  pagato tinyint(3) unsigned NOT NULL,
  modpagamento tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella modpagamento',
  colazione tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella deccolazioni',
  alimentari tinyint(3) unsigned NOT NULL COMMENT 'vedi tabella decalimenti',
  intolleranzealimentari varchar(255) DEFAULT NULL,
  allergiealimentari text COMMENT 'allergie alimentari',
  allergiefarmaci text COMMENT 'allergie farmaci',
  sensoriali tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  psichiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  lis tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  fisiche tinyint(3) unsigned DEFAULT NULL COMMENT 'Y/N',
  patologie text COMMENT 'patologie',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;