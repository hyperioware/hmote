<?php

	include_once 'dbfunctions.php';
	echo "<h3>Setting Up...</h3>";
	
	//CREATE THE NEWSLETTER TABLE
	createTable("newsletter","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, fb_id VARCHAR(255), email VARCHAR(30), first_name VARCHAR(30), last_name VARCHAR(30), date_subscribed TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(first_name), INDEX(last_name), INDEX(email), INDEX(fb_id), INDEX(date_subscribed)");
	
	//CREATE THE MEMBERS TABLE
	createTable("members", "id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, haccess_token TEXT, fb_id VARCHAR(255),email VARCHAR(30),password VARCHAR(40),first_name VARCHAR(50), last_name VARCHAR(50), street VARCHAR(100), city VARCHAR(50), state VARCHAR(2), zipcode VARCHAR(5), gender VARCHAR(6), birthday DATE, marital_status VARCHAR(30), date_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ownBusiness INT(1) DEFAULT 0, uploadedProfilePhoto INT(1) DEFAULT 0, credits INT DEFAULT 0, INDEX(credits), INDEX(email), INDEX(city), INDEX(state), INDEX(zipcode), INDEX(gender), INDEX(birthday), INDEX(marital_status), INDEX(date_joined), INDEX(ownBusiness),INDEX(fb_id)");
	
	//CREATE THE BUSINESSES TABLE
	createTable("businesses","id VARCHAR(10) PRIMARY KEY, memberID INT, name VARCHAR(100), email VARCHAR(100), street VARCHAR(100), city VARCHAR(50), state VARCHAR(2), zipcode VARCHAR(5), mobilePhone VARCHAR(12), landPhone VARCHAR(12), fax VARCHAR(12), type VARCHAR(50), date_joined TIMESTAMP DEFAULT CURRENT_TIMESTAMP, facebook VARCHAR(255), twitter VARCHAR(255), website VARCHAR(255), INDEX(city), INDEX(state), INDEX(zipcode), INDEX(type), INDEX(date_joined)");
	
	//CREATE THE ORG_MEMBERS TABLE
	createTable("org_members","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, org_id INT, member_id INT, title VARCHAR(25), parent_node INT, admin INT(1) DEFAULT 0, INDEX(org_id),INDEX(member_id),INDEX(title),INDEX(admin)");
	
	//CREATE THE NEWS TABLE
	createTable("news","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, news_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, title VARCHAR(255), body TEXT(500), INDEX(news_date)");
	
	//CREATE THE FAVORITES TABLE
	createTable("favorites", "id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, org_id VARCHAR(10), FOREIGN KEY(org_id) REFERENCES businesses(id) ON DELETE CASCADE, member_id INT UNSIGNED, FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE CASCADE, date_favorited TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(org_id), INDEX(member_id)");
	
	//CREATE THE APPLICATIONS TABLE
	createTable("apps","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, business_id INT,title VARCHAR(255), description TEXT(500), INDEX(title)");
	
	//CREATE THE CALENDAR TABLE
	createTable("calendar","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), description TEXT(500), city VARCHAR(30), state VARCHAR(2), zipcode VARCHAR(5), picture_used INT(1) DEFAULT 0, org_id VARCHAR(10), FOREIGN KEY(org_id) REFERENCES businesses(id) ON DELETE CASCADE, member_id INT UNSIGNED, FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE CASCADE, date_start DATE, start_time VARCHAR(5), end_time VARCHAR(5), all_day INT(1) DEFAULT 0, recurring INT(1) DEFAULT 0, recur_increment VARCHAR(50), price DECIMAL(9,2), exposure_level VARCHAR(255), max_attendees INT DEFAULT 0, INDEX(org_id), INDEX(member_id), INDEX(city), INDEX(state), INDEX(zipcode), INDEX(date_start), INDEX(start_time), INDEX(end_time), INDEX(price), INDEX(exposure_level)");
	
	//CREATE THE EVENT ATTENDEES TABLE
	createTable("event_attendees","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, event_id INT UNSIGNED, FOREIGN KEY(event_id) REFERENCES calendar(id) ON DELETE CASCADE, member_id INT, date_of_status TIMESTAMP, status VARCHAR(255), INDEX(status), INDEX(event_id), INDEX(member_id), INDEX(date_of_status)");
	
	//CREATE THE PRODUCTS TABLE
	createTable("products","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, business_id INT, name VARCHAR(255), description TEXT(500), price_in_dollars DECIMAL(4,2), price_in_credits INT, type VARCHAR(255), INDEX(price_in_dollars), INDEX(price_in_credits), INDEX(type)");
	
	//CREATE THE TRANSACTIONS TABLE
	createTable("transactions","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, productID INT, orderID INT UNSIGNED, FOREIGN KEY (orderID) REFERENCES orders(id) ON DELETE CASCADE, price_in_dollars DECIMAL(4,2), price_in_credits INT, quantity INT, INDEX(productID), INDEX(orderID)");
	
	//CREATE THE ORDERS TABLE
	createTable("orders","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, business_id INT, FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE, member_id INT,  date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(business_id), INDEX(member_id), INDEX(date)");
	
	//CREATE THE REVIEWS TABLE
	createTable("reviews","id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, business_id INT, FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE, member_id INT, rating INT(1), review TEXT(500), date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX(business_id), INDEX(member_id), INDEX(date), INDEX(rating)");
?>