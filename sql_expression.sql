CREATE TABLE cards (
	Name VARCHAR(254),
	Address VARCHAR(254),
	Price VARCHAR(64),
	Date INTEGER(64)
);

CREATE TABLE price (
	Name_currency VARCHAR(254),
	RateExchangeUAH VARCHAR(64),
	Date INTEGER(64)
);

CREATE TRIGGER cards_date_created 
	BEFORE INSERT ON cards 
	FOR EACH ROW
	SET 
	new.Date = UNIX_TIMESTAMP(NOW());

CREATE TRIGGER price_date_created 
	BEFORE INSERT ON price 
	FOR EACH ROW
	SET 
	new.Date = UNIX_TIMESTAMP(NOW());
