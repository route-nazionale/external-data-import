CREATE VIEW countclanpeople AS select g.nome , r.idgruppo,r.idunitagruppo,count(r.codicecensimento) as numbers from ragazzo r join gruppi g on r.idgruppo = g.idgruppo and r.idunitagruppo = g.unita group by r.idgruppo, r.idunitagruppo;

CREATE VIEW routetounita  AS select g.route,CONCAT(de.lettera,'',g.ordgruppo) as codicegruppo, g.idunita from decregione de join gemellaggio g on de.abr = g.regione;

CREATE VIEW routenumbers AS select * from countclanpeople cp join routetounita ru on cp.idgruppo = ru.codicegruppo and cp.idunitagruppo = ru.idunita;


CREATE VIEW ragazziCibo AS select r.idgruppo,r.idunitagruppo from ragazzo r where r.allergiealimentari IS NOT NULL AND r.intolleranzealimentari IS NOT NULL;



-- molise, umbria, liguria, toscana, sicilia, veneto, sargegna
-- select route, sum(numbers) from routenumbers r group by route