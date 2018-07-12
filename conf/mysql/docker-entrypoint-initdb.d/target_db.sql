-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- 主機: localhost
-- 產生時間： 2018 年 07 月 12 日 11:31
-- 伺服器版本: 5.7.22-0ubuntu0.16.04.1
-- PHP 版本： 7.0.30-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `demo`
--

-- --------------------------------------------------------

--
-- 資料表結構 `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(255) NOT NULL,
  `data` text,
  `ip` varchar(45) DEFAULT NULL,
  `agent` varchar(300) DEFAULT NULL,
  `stamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 資料表的匯出資料 `sessions`
--

INSERT INTO `sessions` (`session_id`, `data`, `ip`, `agent`, `stamp`) VALUES
('0bp8abohpca8maibqoo9ckpr84', 'csrf|s:27:\"2el11biq2e80s.3gt7b4ai9ls0s\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126406),
('0cjomadd7bglml24kcl1vb8rm2', 'csrf|s:6:\"wdfghn\";', '174.62.105.205', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36', 1515740261),
('0fj1q0668rbq7s5g3brrs92vd6', 'csrf|s:27:\"2el11biq2e80s.2sc6cbsb1268w\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513115158),
('0n0iqgf71gq38tmbhd8ikab8f3', 'csrf|s:27:\"2el11biq2e80s.19ve45z991g7v\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126526),
('11npaljmtqp4qtr09aabjm2ot2', 'csrf|s:26:\"2el11biq2e80s.9uclemi3ie7k\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127430),
('1ho0ev69kqv24q2n1db3iecdb1', 'csrf|s:6:\"wdfghn\";', '5.8.10.202', 'Go-http-client/1.1', 1531368223),
('1q5o0jq3nhttijiko5s1o99f94', 'csrf|s:6:\"wdfghn\";', '223.136.168.170', 'Mozilla/5.0 (Linux; Android 7.1.1; G8232 Build/41.2.A.0.191; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36 [FB_IAB/Orca-Android;FBAV/147.0.0.25.86;]', 1515740777),
('272kplevred51e4d18ors7j132', 'csrf|s:6:\"wdfghn\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36 FirePHP/0.7.4', 1531303856),
('2dikv8iga6i0mvhahek890lsp2', 'csrf|s:6:\"wdfghn\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513114460),
('2k58kkmeaen8e0u93cf23kumt5', 'csrf|s:6:\"wdfghn\";', '159.65.168.252', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1', 1531362082),
('3dd19nqja9m6o7kdr6gu3nhsr6', 'csrf|s:26:\"2el11biq2e80s.sm5yychx5sut\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513116200),
('3i9gnrlp3i8iueea3iu3ofg6k3', 'csrf|s:6:\"wdfghn\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513114662),
('3qvp5qu0c9m5smclsifadkcm96', 'csrf|s:6:\"wdfghn\";cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}', '101.8.99.122', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36', 1516757112),
('43m0pf7qi0n0cokh0mj4llg9j0', 'csrf|s:27:\"2el11biq2e80s.36ejrcqvfc4k0\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126786),
('4buosddl50vj4ml6ui8fkgvhv7', 'csrf|s:6:\"wdfghn\";', '199.30.228.144', 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.2.13) Gecko/20101203 Firefox/3.6.13 GTB7.1', 1531386731),
('4n7snvl458hgjtsqptdmqif3k7', 'csrf|s:26:\"2el11biq2e80s.wx4aq4hizgrp\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128595),
('4r3du8hvr08jvrjuv4hv2tc662', 'csrf|s:26:\"2el11biq2e80s.8ugd289ljdg0\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128848),
('5blme0js9aom4p1cf3bsvoj4a3', 'csrf|s:6:\"wdfghn\";', '37.204.153.61', 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0)', 1516115693),
('5fo7cusk6b1lbj19jh3cidetm3', 'csrf|s:27:\"2el11biq2e80s.193elictdxunh\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126637),
('5hm6vha2qu0qqlinteudaoeb21', 'csrf|s:6:\"wdfghn\";', '199.101.132.161', 'Mozilla/5.0 (compatible; SurdotlyBot/1.0; +http://sur.ly/bot.html)', 1521202027),
('5puh1uomau045d2bslg5j3cri2', 'csrf|s:6:\"wdfghn\";', '203.104.145.28', 'facebookexternalhit/1.1;line-poker/1.0', 1516202179),
('6e5mcmvdb3lb5g8aintmhidej4', 'csrf|s:26:\"2el11biq2e80s.uu85rwunz3fg\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128822),
('6ejta0j101gumqb1nj3203j430', 'csrf|s:6:\"wdfghn\";', '5.8.10.202', 'Go-http-client/1.1', 1531368217),
('6jaat2agqi8ci67480p5udc501', 'csrf|s:27:\"2el11biq2e80s.2gqjzwjgfag4s\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513115076),
('6q1rplnh83eu9cpuvidih3o8m5', 'csrf|s:6:\"wdfghn\";', '104.238.55.102', 'Mozilla/5.0 (Linux; Android 7.0; SM-G925W8 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.98 Mobile Safari/537.36', 1531310803),
('731qiuhpcro212ulvavbgt7j24', 'csrf|s:6:\"wdfghn\";', '92.222.38.67', 'Mozilla/5.1 (Windows NT 6.1; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0', 1531387851),
('7fu4sla6jpkobod9padraveks0', 'csrf|s:6:\"wdfghn\";', '104.238.55.40', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36', 1531311839),
('7rqbd3oevk9l6brji7ejhms474', 'csrf|s:27:\"2el11biq2e80s.3maw2oujlo6cs\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128753),
('82grurcho0neiphbsk128a2ft3', 'cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}csrf|s:27:\"2el11biq2e80s.33ut2vy6ni684\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513129252),
('82tb9n7n4vevepscepdm3k79g1', 'cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}csrf|s:27:\"2el11biq2e80s.1i51f0b115a1e\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513129027),
('88vfulfkt4fue6h36jslr4a115', 'csrf|s:26:\"2el11biq2e80s.lpv916kaope6\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127765),
('8hkik54rql5to7oct41o9pijn3', 'csrf|s:26:\"2el11biq2e80s.xtygzolu4xbo\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128826),
('8t8vn2p751vvs05o0hmh8o6vu5', 'csrf|s:6:\"wdfghn\";', '203.104.145.30', 'facebookexternalhit/1.1;line-poker/1.0', 1514904344),
('8tp0injtugggnvn5gud1i5vjl4', 'csrf|s:27:\"2el11biq2e80s.26fc2lo20y4ko\";cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}', '49.216.103.182', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:57.0) Gecko/20100101 Firefox/57.0', 1514509885),
('8ukbcjdkjbjvle7mc3no57dp94', 'csrf|s:27:\"2el11biq2e80s.3p7txkvr5xwk4\";cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36', 1531302214),
('8vet3pc2ose1cm9semk851f5b2', 'csrf|s:27:\"2el11biq2e80s.3mkrw8ezsdmog\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127439),
('a0mdbasnfshmbt259qgg1cu3v1', 'csrf|s:6:\"wdfghn\";', '45.64.40.20', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 1516171311),
('aucv33vk0e6kp3cvot7ejmbi51', 'csrf|s:6:\"wdfghn\";', '5.8.10.202', 'Go-http-client/1.1', 1531368227),
('b2k905odcvv7j1ugpmb13h1l51', 'csrf|s:27:\"2el11biq2e80s.3nz58sey6668o\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513129252),
('b61pa1d76uig1cvdjhrv6invk3', 'csrf|s:6:\"wdfghn\";', '162.244.33.8', 'Mozilla/5.0 (Windows NT 5.1; rv:33.0) Gecko/20100101 Firefox/33.0', 1531376202),
('bcp6bv2e4vrs41ohf4k4nf5607', 'csrf|s:27:\"2el11biq2e80s.3fbq6eoznpk40\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126772),
('c13rhq710fvgre2bqrv312amk7', 'csrf|s:27:\"2el11biq2e80s.3539wtj7f64g4\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513115187),
('c7k2vn6ga6nq0da1rjmfb8jh90', 'csrf|s:27:\"2el11biq2e80s.37i0qahj7sisg\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128560),
('c7p8g6m1cmdf48c86ukilknuk6', 'csrf|s:6:\"wdfghn\";', '199.101.132.161', 'Mozilla/5.0 (compatible; SurdotlyBot/1.0; +http://sur.ly/bot.html)', 1527375950),
('d7ver147ek4jqarbhpojg35ca2', 'csrf|s:27:\"2el11biq2e80s.2fs5egg0cfy84\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126824),
('d9in2cs81j7dt3334qtvkim6n4', 'csrf|s:27:\"2el11biq2e80s.1rbs3y6pvg41q\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128589),
('daqo4nblc6spi4cmnlnpp4g593', 'csrf|s:6:\"wdfghn\";', '173.252.86.72', 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)', 1515740253),
('dg9au3ug6rj2erne8q2pn2d8p0', 'csrf|s:27:\"2el11biq2e80s.3q5vsmuvs1wk8\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126734),
('dgevo23r1pfe5vfeodvaa3eug3', 'csrf|s:6:\"wdfghn\";', '208.88.224.217', 'Mozilla/5.0 (Linux; Android 4.3; Galaxy Nexus Build/JWR67B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.117 Mobile Safari/537.36', 1518441910),
('dll4kdtd74i6nde7taur041i74', 'csrf|s:27:\"2el11biq2e80s.265uq6ozh5og0\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128731),
('ds33491sfpsm386i0ltjqsgrj6', 'csrf|s:27:\"2el11biq2e80s.1itgndd959tt7\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126628),
('e1katj6sjhunvfd0c4ir0qoio5', 'csrf|s:6:\"wdfghn\";', '5.8.10.202', 'Go-http-client/1.1', 1531368225),
('ed66bhf6grjc8d8io6a2ckjf80', 'csrf|s:6:\"wdfghn\";', '54.237.196.227', 'Mozilla/5.0 (compatible; SurdotlyBot/1.0; +http://sur.ly/bot.html)', 1523838638),
('ek4s60vh3o276n3inpbmqqp041', 'csrf|s:6:\"wdfghn\";', '199.101.132.161', 'Mozilla/5.0 (Linux; Android 4.3; Galaxy Nexus Build/JWR67B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.117 Mobile Safari/537.36', 1529078610),
('esjuv4dtqnrsmbacjs8q3ljpa0', 'csrf|s:27:\"2el11biq2e80s.1gpi4dldf20kn\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127952),
('f2fsmt39njb864slne45d73670', 'cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}csrf|s:27:\"2el11biq2e80s.3oxyz8vk2zs48\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128854),
('fp54tqplm9io2stjhb38a6ltl1', 'csrf|s:27:\"2el11biq2e80s.1uxuxko5pacjb\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128570),
('fvq2cmtp4fmv90rh7c7caoedp4', 'csrf|s:27:\"2el11biq2e80s.1wzb051w4x1si\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128701),
('g6nnv5e8eja25uh5anccgdbl25', 'csrf|s:27:\"2el11biq2e80s.315xj4amopkw8\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126513),
('ga74oep9iign212a0h433c1so5', 'csrf|s:26:\"2el11biq2e80s.d8vgjhqytqrd\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126605),
('ga81do1sbfo6pvk9ep64mq9qp0', 'csrf|s:6:\"wdfghn\";', '199.101.132.161', 'Mozilla/5.0 (compatible; SurdotlyBot/1.0; +http://sur.ly/bot.html)', 1518041869),
('gqtvhtcif40s8pgevq9rda19s0', 'csrf|s:27:\"2el11biq2e80s.2lwh4px5cuw4w\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513114876),
('iasgtifdso4sjplualsoavcdm3', 'csrf|s:27:\"2el11biq2e80s.38kdb2mvugaok\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127176),
('ip2ncun5k5l9cod0iutvd145p3', 'csrf|s:26:\"2el11biq2e80s.tfm8wu7rljwa\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128854),
('j8iacr6bn0tmcjo3ai2f9d1mc3', 'csrf|s:27:\"2el11biq2e80s.22dsnlnzou800\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126827),
('je0ph8hi3ain1lel24m1129cu3', 'csrf|s:6:\"wdfghn\";', '5.8.10.202', 'Go-http-client/1.1', 1531368229),
('jnpj039ph5upm3qrs8qip402v0', 'csrf|s:27:\"2el11biq2e80s.1eoc5ebs1wi1t\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126420),
('joua33k3ctppbri5mdebnho1k1', 'csrf|s:6:\"wdfghn\";', '5.8.10.202', 'Go-http-client/1.1', 1531368219),
('kk2fbp0iisuqo12kd8akrgaqr5', 'csrf|s:6:\"wdfghn\";', '13.57.233.99', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36', 1531384182),
('kkarpp4e3762nej6ld0cm8h525', 'csrf|s:6:\"wdfghn\";', '114.34.11.51', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7', 1513247540),
('kq4r9k0eiirsmc62nnuesimm92', 'csrf|s:6:\"wdfghn\";', '203.104.145.48', 'facebookexternalhit/1.1;line-poker/1.0', 1515061135),
('l6n4b1knulerigprtm3mbgfhh5', 'csrf|s:27:\"2el11biq2e80s.1lk23k8hi02kj\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126650),
('mdvjhq9dlcbajm2usq7j514dr4', 'csrf|s:26:\"2el11biq2e80s.k1g4isyiyjyz\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128774),
('mq6updm0bqrde1iolh8p14u651', 'csrf|s:26:\"2el11biq2e80s.c004vzwr7o6a\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126781),
('mtjvmc522bg32najdniqgkua21', 'csrf|s:27:\"2el11biq2e80s.35xuad4v83y8k\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126441),
('n1beiklcr30frtheoboakthl83', 'csrf|s:6:\"wdfghn\";', '138.68.58.238', 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_4 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Mobile/13G35 Safari Line/8.8.0', 1531359848),
('n3osv3edh106bavvbaan1kgne2', 'csrf|s:6:\"wdfghn\";', '118.169.74.163', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36', 1531384985),
('nn7acen4o790b8eq6i0grjpam1', 'csrf|s:6:\"wdfghn\";', '188.226.190.35', 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15F79 Safari Line/8.9.0', 1531326479),
('o86ffs7sk4lne9ao72uohoai23', 'csrf|s:6:\"wdfghn\";', '184.105.139.70', '', 1531316162),
('od9i7acit6ue3a4fsfvljr1c10', 'csrf|s:26:\"2el11biq2e80s.amfxbb3yz0ku\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128000),
('og4e1dnl93v9ah344nrcii4b83', 'csrf|s:27:\"2el11biq2e80s.24vgx6ueiwbo0\";', '95.84.128.167', 'Mozilla/5.0 (SMART-TV; Linux; Tizen 2.4.0) AppleWebkit/538.1 (KHTML, like Gecko) SamsungBrowser/1.1 TV Safari/538.1', 1518922888),
('orl92rbtdfhteg86au1ahq24p4', 'csrf|s:27:\"2el11biq2e80s.2jcbzun1rio0w\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513129027),
('oukcejnmo9bhdib8hepdljks15', 'csrf|s:6:\"wdfghn\";', '74.117.181.205', 'Mozilla/5.0 (compatible; SurdotlyBot/1.0; +http://sur.ly/bot.html)', 1529078611),
('phbefmac1tsubhb1140l52epj1', 'csrf|s:6:\"wdfghn\";', '122.116.43.31', 'facebookexternalhit/1.1;line-poker/1.0', 1531310095),
('q0dho3gs3fjg0ik2n1ajvb2944', 'cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}csrf|s:27:\"2el11biq2e80s.2hkrzudglfcwg\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128774),
('q28b0o515na0ckb04ggal5omu7', 'csrf|s:6:\"wdfghn\";', '118.163.78.199', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36', 1515061212),
('qh7cr1vp9edlm7gncjqct7lug3', 'csrf|s:6:\"wdfghn\";', '122.116.43.31', 'facebookexternalhit/1.1;line-poker/1.0', 1531310094),
('qjpqon75b673rciqifs6km6nc6', 'csrf|s:6:\"wdfghn\";', '122.224.129.234', '', 1531325818),
('qur0k00ksmi94ksvm666bs8eo6', 'csrf|s:27:\"2el11biq2e80s.1yncpvrug8748\";cs|a:3:{s:4:\"name\";s:5:\"admin\";s:2:\"id\";s:1:\"1\";s:9:\"has_login\";i:1;}', '223.136.41.40', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36', 1516334671),
('qvqbnorntggai5usn7fsdohga6', 'csrf|s:6:\"wdfghn\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36', 1513113721),
('qvqcv12kson08n34hbhq6mofq0', 'csrf|s:26:\"2el11biq2e80s.oa0mhsjlbkyq\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128694),
('r6sqkb19ishcrtihs4aoe0bat5', 'csrf|s:6:\"wdfghn\";', '54.237.196.227', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/534.34 (KHTML, like Gecko) Qt/4.8.2 Safari/534.34', 1523838639),
('rmomdelqvhl6tp1r358krc06s0', 'csrf|s:27:\"2el11biq2e80s.1ihw1e8o0b6mr\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128058),
('rvdtkhvntmfq5j5oa55ub2q932', 'csrf|s:27:\"2el11biq2e80s.3rkqnp4t0xes0\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127050),
('sa5h956qvmtde35ido82ep1951', 'csrf|s:27:\"2el11biq2e80s.11dixpj3hgj2y\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513128938),
('se8ednmarv0ftscckuhat828c6', 'csrf|s:27:\"2el11biq2e80s.27sctfezms4ko\";', '122.116.43.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513117365),
('t1vhjqkv0241cs7ed2llc2von6', 'csrf|s:26:\"2el11biq2e80s.5p0t9xoa7rtv\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126961),
('ubscciv6afp1ov59eidkammvd7', 'csrf|s:27:\"2el11biq2e80s.2ra7t57rvp6ow\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513126169),
('v4qa4db618juvv9k4ccehigpl3', 'csrf|s:6:\"wdfghn\";', '173.252.92.245', 'facebookexternalhit/1.1', 1515740254),
('v73v6rlfldopqi8r4huej7o0c5', 'csrf|s:27:\"2el11biq2e80s.2ftnijscso4kk\";', '101.12.1.8', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36 FirePHP/0.7.4', 1513127622),
('vc24iklj0dupem4ku4dv6mo921', 'csrf|s:6:\"wdfghn\";', '38.130.180.144', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36', 1531311434),
('vhvc4rs0ch7cocr9jsqdihd3k6', 'csrf|s:6:\"wdfghn\";', '74.117.179.19', 'Mozilla/5.0 (compatible; SurdotlyBot/1.0; +http://sur.ly/bot.html)', 1518441924),
('vn9rka5eo1qrrab0t62qchvra3', 'csrf|s:6:\"wdfghn\";', '111.240.191.222', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36', 1516202308);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_adv`
--

CREATE TABLE `tbl_adv` (
  `id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `counter` int(11) NOT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Disabled',
  `sorter` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(10) DEFAULT NULL,
  `end_date` date NOT NULL,
  `uri` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `background` varchar(255) NOT NULL,
  `summary` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_author`
--

CREATE TABLE `tbl_author` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `slug` varchar(255) NOT NULL,
  `rel_tag` varchar(255) DEFAULT NULL,
  `online_date` date DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(255) NOT NULL,
  `info` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_category`
--

CREATE TABLE `tbl_category` (
  `id` int(11) NOT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Disabled',
  `parent_id` int(11) NOT NULL,
  `sorter` int(11) DEFAULT '0',
  `slug` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_category_lang`
--

CREATE TABLE `tbl_category_lang` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `content` text CHARACTER SET utf8,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_contact`
--

CREATE TABLE `tbl_contact` (
  `id` int(11) NOT NULL,
  `status` enum('New','Process','Done') NOT NULL DEFAULT 'New',
  `type` varchar(50) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `other` text,
  `response` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_dictionary`
--

CREATE TABLE `tbl_dictionary` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) NOT NULL,
  `content` text,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_dictionary_draft`
--

CREATE TABLE `tbl_dictionary_draft` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) NOT NULL,
  `content` text,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_media`
--

CREATE TABLE `tbl_media` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `content` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_member`
--

CREATE TABLE `tbl_member` (
  `id` int(11) NOT NULL,
  `status` enum('New','Verified','Freeze') DEFAULT 'New',
  `account` varchar(45) DEFAULT NULL,
  `pwd` varchar(45) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_member_meta`
--

CREATE TABLE `tbl_member_meta` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `k` varchar(50) DEFAULT NULL,
  `v` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_menu`
--

CREATE TABLE `tbl_menu` (
  `id` int(11) NOT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Disabled',
  `parent_id` int(11) DEFAULT '0',
  `uri` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(30) NOT NULL,
  `sorter` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pic` varchar(150) DEFAULT NULL,
  `summary` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `last_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) NOT NULL,
  `insert_user` int(11) NOT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 資料表的匯出資料 `tbl_menu`
--

INSERT INTO `tbl_menu` (`id`, `status`, `parent_id`, `uri`, `type`, `sorter`, `title`, `pic`, `summary`, `last_ts`, `last_user`, `insert_user`, `insert_ts`) VALUES
(1, 'Enabled', 0, '/nav', 'None', 1, '上方導覽列', '', '', '2017-01-19 13:09:45', 1, 1, NULL),
(2, 'Enabled', 0, '/sidebar', 'None', 3, '右側欄', '', '', '2015-12-10 02:02:02', 1, 1, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_menu_lang`
--

CREATE TABLE `tbl_menu_lang` (
  `id` int(11) NOT NULL,
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_option`
--

CREATE TABLE `tbl_option` (
  `id` int(11) NOT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `loader` enum('Preload','Demand') NOT NULL DEFAULT 'Demand',
  `group` varchar(50) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content` text,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 資料表的匯出資料 `tbl_option`
--

INSERT INTO `tbl_option` (`id`, `status`, `loader`, `group`, `name`, `content`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Enabled', 'Demand', 'page', 'title', 'F3CMS DEMO', '2017-12-28 21:49:32', 1, '2015-12-29 14:43:32', 1),
(2, 'Enabled', 'Demand', 'page', 'keyword', 'key1,key2,key3', '2017-12-29 09:44:23', 1, '2015-12-29 14:44:11', 1),
(4, 'Enabled', 'Demand', 'page', 'img', 'demo.png', '2017-12-28 21:45:11', 1, '2015-12-29 14:46:44', 1),
(5, 'Enabled', 'Demand', 'social', 'facebook_page', 'https://www.facebook.com/', '2015-12-29 18:35:46', 1, '2015-12-29 18:35:46', 1),
(8, 'Enabled', 'Preload', 'default', 'contact_mail', 'sense.info.co@gmail.com', '2016-02-10 06:58:13', 1, '2016-02-02 10:08:41', 1),
(12, 'Enabled', 'Demand', 'page', 'ga', '', '2017-03-27 02:52:28', 1, '2016-05-04 07:51:12', 1),
(17, 'Enabled', 'Demand', 'page', 'pagetest', '', '2017-12-28 23:24:26', 1, '2017-12-28 23:24:26', 1),
(18, 'Enabled', 'Demand', '這是G', '這是N', '這是C', '2017-12-29 09:24:48', 1, '2017-12-29 09:24:48', 1),
(19, 'Enabled', 'Demand', 'Group01', 'soso', 'C1,C2,C3', '2017-12-29 09:26:51', 1, '2017-12-29 09:26:51', 1),
(20, 'Enabled', 'Demand', 'Group02', 'soso2', 'C1,C2,C5', '2017-12-29 09:27:02', 1, '2017-12-29 09:27:02', 1),
(21, 'Enabled', 'Demand', 'Group03', 'soso3', 'C1,C2,C7', '2017-12-29 09:27:11', 1, '2017-12-29 09:27:11', 1),
(22, 'Enabled', 'Demand', 'Group04', 'soso3', 'C1,C2,C9', '2017-12-29 09:27:20', 1, '2017-12-29 09:27:20', 1),
(23, 'Enabled', 'Demand', 'Group05', 'soso4', 'C1,C2,C10', '2017-12-29 09:28:17', 1, '2017-12-29 09:28:17', 1),
(24, 'Enabled', 'Demand', 'Group06', 'soso5', 'C1,C2,C11', '2017-12-29 09:28:28', 1, '2017-12-29 09:28:28', 1),
(25, 'Enabled', 'Demand', 'G1', 'N1', 'C1', '2017-12-29 09:32:02', 1, '2017-12-29 09:32:02', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post`
--

CREATE TABLE `tbl_post` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `content` text,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 資料表的匯出資料 `tbl_post`
--

INSERT INTO `tbl_post` (`id`, `status`, `type`, `slug`, `title`, `pic`, `content`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(3, 'Enabled', 'Ancestor', '/about', '關於我們', '', '<h2>程得時營靜班有就不地</h2>\r\n<div id=\'product-component-8cbb6f9a3ff\'></div>\r\n<script type=\"text/javascript\">\r\n/*<![CDATA[*/\r\n\r\n(function() {\r\n    var scriptURL = \'https://sdks.shopifycdn.com/buy-button/latest/buy-button-storefront.min.js\';\r\n    if (window.ShopifyBuy) {\r\n        if (window.ShopifyBuy.UI) {\r\n            ShopifyBuyInit();\r\n        } else {\r\n            loadScript();\r\n        }\r\n    } else {\r\n        loadScript();\r\n    }\r\n\r\n    function loadScript() {\r\n        var script = document.createElement(\'script\');\r\n        script.async = true;\r\n        script.src = scriptURL;\r\n        (document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(script);\r\n        script.onload = ShopifyBuyInit;\r\n    }\r\n\r\n    function ShopifyBuyInit() {\r\n        var client = ShopifyBuy.buildClient({\r\n            domain: \'soking-little-ec.myshopify.com\',\r\n            apiKey: \'8612a1686f02b51ed12a36265b4ec2c1\',\r\n            appId: \'6\',\r\n        });\r\n\r\n        ShopifyBuy.UI.onReady(client).then(function(ui) {\r\n            ui.createComponent(\'product\', {\r\n                id: [416846905380],\r\n                node: document.getElementById(\'product-component-8cbb6f9a3ff\'),\r\n                moneyFormat: \'%24%7B%7Bamount%7D%7D\',\r\n                options: {\r\n                    \"product\": {\r\n                        \"buttonDestination\": \"checkout\",\r\n                        \"variantId\": \"all\",\r\n                        \"width\": \"240px\",\r\n                        \"contents\": {\r\n                            \"imgWithCarousel\": false,\r\n                            \"variantTitle\": false,\r\n                            \"description\": false,\r\n                            \"buttonWithQuantity\": true,\r\n                            \"button\": false,\r\n                            \"quantity\": false\r\n                        },\r\n                        \"text\": {\r\n                            \"button\": \"現在購買\"\r\n                        },\r\n                        \"styles\": {\r\n                            \"product\": {\r\n                                \"@media (min-width: 601px)\": {\r\n                                    \"max-width\": \"100%\",\r\n                                    \"margin-left\": \"0\",\r\n                                    \"margin-bottom\": \"50px\"\r\n                                }\r\n                            },\r\n                            \"button\": {\r\n                                \"background-color\": \"#7be5ee\",\r\n                                \"font-size\": \"14px\",\r\n                                \"padding-top\": \"15px\",\r\n                                \"padding-bottom\": \"15px\",\r\n                                \"padding-left\": \"22px\",\r\n                                \"padding-right\": \"22px\",\r\n                                \":hover\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                },\r\n                                \":focus\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                }\r\n                            },\r\n                            \"title\": {\r\n                                \"color\": \"#2f66c3\"\r\n                            },\r\n                            \"quantityInput\": {\r\n                                \"font-size\": \"14px\",\r\n                                \"padding-top\": \"15px\",\r\n                                \"padding-bottom\": \"15px\"\r\n                            },\r\n                            \"compareAt\": {\r\n                                \"font-size\": \"12px\"\r\n                            }\r\n                        }\r\n                    },\r\n                    \"cart\": {\r\n                        \"contents\": {\r\n                            \"button\": true\r\n                        },\r\n                        \"styles\": {\r\n                            \"button\": {\r\n                                \"background-color\": \"#7be5ee\",\r\n                                \"font-size\": \"14px\",\r\n                                \"padding-top\": \"15px\",\r\n                                \"padding-bottom\": \"15px\",\r\n                                \":hover\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                },\r\n                                \":focus\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                }\r\n                            },\r\n                            \"footer\": {\r\n                                \"background-color\": \"#ffffff\"\r\n                            }\r\n                        }\r\n                    },\r\n                    \"modalProduct\": {\r\n                        \"contents\": {\r\n                            \"img\": false,\r\n                            \"imgWithCarousel\": true,\r\n                            \"variantTitle\": false,\r\n                            \"buttonWithQuantity\": true,\r\n                            \"button\": false,\r\n                            \"quantity\": false\r\n                        },\r\n                        \"styles\": {\r\n                            \"product\": {\r\n                                \"@media (min-width: 601px)\": {\r\n                                    \"max-width\": \"100%\",\r\n                                    \"margin-left\": \"0px\",\r\n                                    \"margin-bottom\": \"0px\"\r\n                                }\r\n                            },\r\n                            \"button\": {\r\n                                \"background-color\": \"#7be5ee\",\r\n                                \"font-size\": \"14px\",\r\n                                \"padding-top\": \"15px\",\r\n                                \"padding-bottom\": \"15px\",\r\n                                \"padding-left\": \"22px\",\r\n                                \"padding-right\": \"22px\",\r\n                                \":hover\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                },\r\n                                \":focus\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                }\r\n                            },\r\n                            \"quantityInput\": {\r\n                                \"font-size\": \"14px\",\r\n                                \"padding-top\": \"15px\",\r\n                                \"padding-bottom\": \"15px\"\r\n                            }\r\n                        }\r\n                    },\r\n                    \"toggle\": {\r\n                        \"styles\": {\r\n                            \"toggle\": {\r\n                                \"background-color\": \"#7be5ee\",\r\n                                \":hover\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                },\r\n                                \":focus\": {\r\n                                    \"background-color\": \"#6fced6\"\r\n                                }\r\n                            },\r\n                            \"count\": {\r\n                                \"font-size\": \"14px\"\r\n                            }\r\n                        }\r\n                    },\r\n                    \"productSet\": {\r\n                        \"styles\": {\r\n                            \"products\": {\r\n                                \"@media (min-width: 601px)\": {\r\n                                    \"margin-left\": \"-20px\"\r\n                                }\r\n                            }\r\n                        }\r\n                    }\r\n                }\r\n            });\r\n        });\r\n    }\r\n})();\r\n/*]]>*/\r\n</script>\r\n<p>選實坐年不開國回程孩工慢親此辦觀古，金居裡省存究還後量目人；一身到開去非然的山他情過趣。上以政，軍以策改調文成五！老代水是治高他進成，吸化樣流一寫有感、便改系站沒良參血例些政，前確到美立影老；來華研，大房該告手口度運密增、富百有良技，黃機然不石司處最想廣進長量一演者月大錯在眾教這位利：的石理，說果個一！沒化成年。了住組經活所自來一是……爸知得分性意，境人行，有來研後感特不很媽快那是，現業比新主義道了不什總，以的心書機我，我不委，卻想量度族使花水太子時喜進而動少全位了不推人是能觀時洋安媽眾歷，重花興備本平？積接條著年？持隨分，國氣特反流式改有有每辦裡明過關前教面急！超器中夜子，可我件布天萬想小可害時著行進影支；電下識二主飯見業一銀基沒白文旅資裡由回，園單數表岸光容轉不。大從服軍不想？媽成和解動方人原覺深親下這校另到業會不往！</p>\r\n<p>府質院有意、今覺馬能以學金。空是不場看要例有、半能型其，竟星獨，變住亮復來臺口高自給答他山時獎朋界上片在現走模怎證安，黃北夜任方，氣費女年意精業較基坐人收位了生的陽比過象已、何看我片整進省位念計以照水家正己們過因。</p>\r\n<div id=\'product-component-411a4202e13\'></div>\r\n<script type=\"text/javascript\">\r\n/*<![CDATA[*/\r\n(function() { var scriptURL = \'https://sdks.shopifycdn.com/buy-button/latest/buy-button-storefront.min.js\'; if (window.ShopifyBuy) { if (window.ShopifyBuy.UI) { ShopifyBuyInit(); } else { loadScript(); } } else { loadScript(); }\r\n\r\n    function loadScript() { var script = document.createElement(\'script\');\r\n        script.async = true;\r\n        script.src = scriptURL;\r\n        (document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(script);\r\n        script.onload = ShopifyBuyInit; }\r\n\r\n    function ShopifyBuyInit() { var client = ShopifyBuy.buildClient({ domain: \'soking-little-ec.myshopify.com\', apiKey: \'8612a1686f02b51ed12a36265b4ec2c1\', appId: \'6\', });\r\n        ShopifyBuy.UI.onReady(client).then(function(ui) { ui.createComponent(\'product\', { id: [258873950244], node: document.getElementById(\'product-component-411a4202e13\'), moneyFormat: \'%24%7B%7Bamount%7D%7D\', options: { \"product\": { \"buttonDestination\": \"checkout\", \"variantId\": \"all\", \"width\": \"240px\", \"contents\": { \"imgWithCarousel\": false, \"variantTitle\": false, \"description\": false, \"buttonWithQuantity\": false, \"quantity\": false }, \"text\": { \"button\": \"BUY NOW\" }, \"styles\": { \"product\": { \"text-align\": \"left\", \"@media (min-width: 601px)\": { \"max-width\": \"100%\", \"margin-left\": \"0\", \"margin-bottom\": \"50px\" } }, \"compareAt\": { \"font-size\": \"12px\" } } }, \"cart\": { \"contents\": { \"button\": true }, \"styles\": { \"footer\": { \"background-color\": \"#ffffff\" } } }, \"modalProduct\": { \"contents\": { \"img\": false, \"imgWithCarousel\": true, \"variantTitle\": false, \"buttonWithQuantity\": true, \"button\": false, \"quantity\": false }, \"styles\": { \"product\": { \"@media (min-width: 601px)\": { \"max-width\": \"100%\", \"margin-left\": \"0px\", \"margin-bottom\": \"0px\" } } } }, \"productSet\": { \"styles\": { \"products\": { \"@media (min-width: 601px)\": { \"margin-left\": \"-20px\" } } } } } }); }); } })(); /*]]>*/\r\n</script>', '2017-04-01 15:55:14', 1, '2017-01-17 18:07:53', 1),
(5, 'Enabled', 'Ancestor', '/contact', '聯絡我們', '', '', '2017-04-01 04:22:37', 1, '2017-03-26 02:22:21', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post_draft`
--

CREATE TABLE `tbl_post_draft` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `content` text,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_post_lang`
--

CREATE TABLE `tbl_post_lang` (
  `id` int(11) NOT NULL,
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `content` text CHARACTER SET utf8,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press`
--

CREATE TABLE `tbl_press` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL DEFAULT '0',
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `mode` enum('Article','Slide') NOT NULL DEFAULT 'Article',
  `on_homepage` enum('Yes','No') NOT NULL DEFAULT 'No',
  `on_top` enum('Yes','No') NOT NULL DEFAULT 'No',
  `slug` varchar(255) NOT NULL,
  `rel_tag` varchar(255) DEFAULT NULL,
  `rel_dict` varchar(255) DEFAULT NULL,
  `online_date` date NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keyword` varchar(255) DEFAULT NULL,
  `helper` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `info` varchar(255) DEFAULT NULL,
  `content` text,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_draft`
--

CREATE TABLE `tbl_press_draft` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL DEFAULT '0',
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `mode` enum('Article','Slide') NOT NULL DEFAULT 'Article',
  `on_homepage` enum('Yes','No') NOT NULL DEFAULT 'No',
  `on_top` enum('Yes','No') NOT NULL DEFAULT 'No',
  `slug` varchar(255) NOT NULL,
  `rel_tag` varchar(255) DEFAULT NULL,
  `online_date` date NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `pic` varchar(255) NOT NULL,
  `info` varchar(255) DEFAULT NULL,
  `content` text,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_press_lang`
--

CREATE TABLE `tbl_press_lang` (
  `id` int(11) NOT NULL,
  `type` enum('Ancestor','Draft','Backup') NOT NULL DEFAULT 'Ancestor',
  `lang` varchar(5) NOT NULL DEFAULT 'tw',
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `info` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `content` text CHARACTER SET utf8,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_staff`
--

CREATE TABLE `tbl_staff` (
  `id` int(11) NOT NULL,
  `status` enum('New','Verified','Freeze') DEFAULT 'New',
  `account` varchar(45) DEFAULT NULL,
  `pwd` varchar(45) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 資料表的匯出資料 `tbl_staff`
--

INSERT INTO `tbl_staff` (`id`, `status`, `account`, `pwd`, `last_ts`, `last_user`, `insert_ts`, `insert_user`) VALUES
(1, 'Verified', 'admin', '81dc9bdb52d04dc20036dbd8313ed055', '2017-04-02 18:01:05', 1, '2015-08-05 04:41:20', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `tbl_tag`
--

CREATE TABLE `tbl_tag` (
  `id` int(11) NOT NULL,
  `status` enum('Disabled','Enabled') DEFAULT 'Disabled',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `counter` int(11) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `last_ts` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_user` int(11) DEFAULT NULL,
  `insert_ts` timestamp NULL DEFAULT NULL,
  `insert_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- 資料表索引 `tbl_adv`
--
ALTER TABLE `tbl_adv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`position_id`),
  ADD KEY `uri` (`uri`);

--
-- 資料表索引 `tbl_author`
--
ALTER TABLE `tbl_author`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_category`
--
ALTER TABLE `tbl_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uri` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- 資料表索引 `tbl_category_lang`
--
ALTER TABLE `tbl_category_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lang_pid` (`type`,`lang`,`parent_id`);

--
-- 資料表索引 `tbl_contact`
--
ALTER TABLE `tbl_contact`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_dictionary`
--
ALTER TABLE `tbl_dictionary`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_dictionary_draft`
--
ALTER TABLE `tbl_dictionary_draft`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_media`
--
ALTER TABLE `tbl_media`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_member`
--
ALTER TABLE `tbl_member`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_member_meta`
--
ALTER TABLE `tbl_member_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_meta_user1_idx` (`parent_id`);

--
-- 資料表索引 `tbl_menu`
--
ALTER TABLE `tbl_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- 資料表索引 `tbl_menu_lang`
--
ALTER TABLE `tbl_menu_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lang_pid` (`type`,`lang`,`parent_id`);

--
-- 資料表索引 `tbl_option`
--
ALTER TABLE `tbl_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group` (`group`);

--
-- 資料表索引 `tbl_post`
--
ALTER TABLE `tbl_post`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_post_draft`
--
ALTER TABLE `tbl_post_draft`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_post_lang`
--
ALTER TABLE `tbl_post_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lang_pid` (`type`,`lang`,`parent_id`);

--
-- 資料表索引 `tbl_press`
--
ALTER TABLE `tbl_press`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_press_draft`
--
ALTER TABLE `tbl_press_draft`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_press_lang`
--
ALTER TABLE `tbl_press_lang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lang_pid` (`type`,`lang`,`parent_id`);

--
-- 資料表索引 `tbl_staff`
--
ALTER TABLE `tbl_staff`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `tbl_tag`
--
ALTER TABLE `tbl_tag`
  ADD PRIMARY KEY (`id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `tbl_adv`
--
ALTER TABLE `tbl_adv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_author`
--
ALTER TABLE `tbl_author`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_category`
--
ALTER TABLE `tbl_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_category_lang`
--
ALTER TABLE `tbl_category_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_contact`
--
ALTER TABLE `tbl_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_dictionary`
--
ALTER TABLE `tbl_dictionary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_dictionary_draft`
--
ALTER TABLE `tbl_dictionary_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_media`
--
ALTER TABLE `tbl_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_member`
--
ALTER TABLE `tbl_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_member_meta`
--
ALTER TABLE `tbl_member_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_menu`
--
ALTER TABLE `tbl_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- 使用資料表 AUTO_INCREMENT `tbl_menu_lang`
--
ALTER TABLE `tbl_menu_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_option`
--
ALTER TABLE `tbl_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
--
-- 使用資料表 AUTO_INCREMENT `tbl_post`
--
ALTER TABLE `tbl_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- 使用資料表 AUTO_INCREMENT `tbl_post_draft`
--
ALTER TABLE `tbl_post_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_post_lang`
--
ALTER TABLE `tbl_post_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_press`
--
ALTER TABLE `tbl_press`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_press_draft`
--
ALTER TABLE `tbl_press_draft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_press_lang`
--
ALTER TABLE `tbl_press_lang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用資料表 AUTO_INCREMENT `tbl_staff`
--
ALTER TABLE `tbl_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- 使用資料表 AUTO_INCREMENT `tbl_tag`
--
ALTER TABLE `tbl_tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
