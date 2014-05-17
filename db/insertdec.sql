INSERT INTO periodopartecipazione (id, kkey, description, ruolo)
VALUES
	(1,1,'7 - 9 agosto 2014','LAB'),
	(2,2,'4 - 10 agosto 2014','LAB'),
	(3,3,'7 - 13 agosto 2014','LAB'),
	(4,4,'4 - 13 agosto 2014','LAB'),
	(5,1,'4 - 10 agosto 2014','ONE'),
	(6,2,'7 - 13 agosto 2014','ONE'),
	(7,3,'4 - 13 agosto 2014','ONE'),
	(8,1,'4 - 13 agosto 2014','EXTRA');


INSERT INTO decalimenti (id, kkey, value)
VALUES
	(1,1,'NESSUNA'),
	(2,2,'VEGETARIANO'),
	(3,3,'VEGANO');
	
	
INSERT INTO deccolazioni (id, kkey, value)
VALUES
	(1,1,'LATTE'),
	(2,2,'THE'),
	(3,3,'ALTRO');
		
		
INSERT INTO decruolo (id, kkey, value)
VALUES
	(1,1,'CAPO UNITA'),
	(2,2,'MAESTRO DEI NOVIZI'),
	(3,3,'ASSISTENTE ECCLESIASTICO'),
	(4,4,'AIUTO CAPO UNITA'),
	(5,5,'AIUTO MAESTRO DEI NOVIZI');
			

INSERT INTO modpagamento (id, kkey, description)
VALUES
	(1,1,'Carta di credito'),
	(2,2,'Bonifico'),
	(3,3,'Partecipa con unita RS');