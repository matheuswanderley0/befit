CREATE DATABASE  IF NOT EXISTS `befit_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `befit_system`;
-- MySQL dump 10.13  Distrib 8.0.36, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: befit_system
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `convites`
--

DROP TABLE IF EXISTS `convites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `convites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `personal_id` int NOT NULL,
  `aluno_id` int NOT NULL,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','aceito','recusado') DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `personal_id` (`personal_id`),
  KEY `aluno_id` (`aluno_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `convites`
--

LOCK TABLES `convites` WRITE;
/*!40000 ALTER TABLE `convites` DISABLE KEYS */;
INSERT INTO `convites` VALUES (1,3,11,'2025-12-02 17:15:50','pendente'),(2,3,12,'2025-12-02 17:17:11','aceito');
/*!40000 ALTER TABLE `convites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `especialidades`
--

DROP TABLE IF EXISTS `especialidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `especialidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `especialidades`
--

LOCK TABLES `especialidades` WRITE;
/*!40000 ALTER TABLE `especialidades` DISABLE KEYS */;
INSERT INTO `especialidades` VALUES (1,'Hipertrofia'),(2,'Emagrecimento'),(3,'Funcional'),(4,'Yoga'),(5,'Crossfit');
/*!40000 ALTER TABLE `especialidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exercicios`
--

DROP TABLE IF EXISTS `exercicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exercicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ficha_id` int NOT NULL,
  `nome_exercicio` varchar(100) NOT NULL,
  `ordem` int DEFAULT '0',
  `observacao` text,
  PRIMARY KEY (`id`),
  KEY `ficha_id` (`ficha_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exercicios`
--

LOCK TABLES `exercicios` WRITE;
/*!40000 ALTER TABLE `exercicios` DISABLE KEYS */;
INSERT INTO `exercicios` VALUES (1,1,'Supino Reto com Barra',1,'Controlar a descida'),(2,1,'Crucifixo Inclinado',2,NULL),(3,1,'Tríceps Corda',3,'Fazer até a falha na última'),(4,2,'Puxada Alta',1,NULL),(5,2,'Remada Curvada',2,NULL),(6,2,'Rosca Direta',3,NULL),(7,3,'Supino inclinado',1,NULL),(8,4,'Leg Press',1,NULL),(9,5,'Pulley',1,NULL);
/*!40000 ALTER TABLE `exercicios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fichas_treino`
--

DROP TABLE IF EXISTS `fichas_treino`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fichas_treino` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aluno_id` int NOT NULL,
  `personal_id` int NOT NULL,
  `nome_ficha` varchar(50) NOT NULL,
  `descricao` text,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `aluno_id` (`aluno_id`),
  KEY `personal_id` (`personal_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fichas_treino`
--

LOCK TABLES `fichas_treino` WRITE;
/*!40000 ALTER TABLE `fichas_treino` DISABLE KEYS */;
INSERT INTO `fichas_treino` VALUES (1,10,1,'Treino A - Peito e Tríceps','Foco em força. Descanso de 90s entre séries.','2025-12-01 13:31:59'),(2,10,1,'Treino B - Costas e Bíceps','Foco em volume de treino.','2025-12-01 13:31:59'),(3,12,3,'Peito','','2025-12-02 17:25:12'),(4,12,3,'Perna','','2025-12-02 17:27:11'),(5,12,3,'Costas','','2025-12-02 17:28:21');
/*!40000 ALTER TABLE `fichas_treino` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_peso`
--

DROP TABLE IF EXISTS `historico_peso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_peso` (
  `id` int NOT NULL AUTO_INCREMENT,
  `aluno_id` int NOT NULL,
  `peso` decimal(5,2) NOT NULL,
  `data_registro` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aluno_id` (`aluno_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_peso`
--

LOCK TABLES `historico_peso` WRITE;
/*!40000 ALTER TABLE `historico_peso` DISABLE KEYS */;
INSERT INTO `historico_peso` VALUES (1,10,88.00,'2024-05-01'),(2,10,87.20,'2024-06-01'),(3,10,86.50,'2024-07-01'),(4,10,85.00,'2024-08-01'),(5,10,84.20,'2024-09-01'),(6,10,82.50,'2024-10-01');
/*!40000 ALTER TABLE `historico_peso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mensagens`
--

DROP TABLE IF EXISTS `mensagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensagens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `remetente_id` int NOT NULL,
  `destinatario_id` int NOT NULL,
  `mensagem` text NOT NULL,
  `data_envio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lida` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `remetente_id` (`remetente_id`),
  KEY `destinatario_id` (`destinatario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensagens`
--

LOCK TABLES `mensagens` WRITE;
/*!40000 ALTER TABLE `mensagens` DISABLE KEYS */;
INSERT INTO `mensagens` VALUES (1,10,1,'Professor, terminei o treino A. Senti bastante o ombro no supino.','2024-10-26 13:30:00',1),(2,1,10,'Boa, João! No próximo, tente fechar um pouco mais a pegada. Vai aliviar o ombro.','2024-10-26 13:35:00',1),(3,10,1,'Beleza, vou testar amanhã. Obrigado!','2024-10-26 13:36:00',0),(4,3,1,'ola','2025-12-01 13:49:08',0),(5,3,1,'jygfftftrtrdtrfd','2025-12-01 14:12:00',0),(6,3,1,'fera','2025-12-01 14:12:06',0),(7,12,1,'Salve','2025-12-01 14:55:37',0),(8,1,12,'Opa','2025-12-01 14:56:24',0),(9,1,12,'Bao demais Fi','2025-12-01 14:56:51',0),(10,10,2,'a','2025-12-02 16:53:36',0),(11,10,4,'Olá','2025-12-02 16:55:37',0),(12,10,2,'Olá','2025-12-02 16:55:40',0),(13,3,10,'Olá João Silva! Gostaria de convidar você para fazer parte do meu time de alunos. Vamos treinar juntos?','2025-12-02 17:09:55',0),(14,3,11,'Olá Maria Oliveira! Enviei um convite oficial para você ser meu aluno. Aceite no seu painel inicial para começarmos!','2025-12-02 17:15:50',0),(15,3,12,'Olá Pedro Santos! Enviei um convite oficial para você ser meu aluno. Aceite no seu painel inicial para começarmos!','2025-12-02 17:17:11',0),(16,12,1,'Bão','2025-12-02 17:28:59',0),(17,12,3,'Olá','2025-12-02 17:29:44',0),(18,12,3,'Positivo','2025-12-02 17:29:57',0),(19,3,12,'Muito obrigado pedro','2025-12-02 17:31:47',0),(20,12,3,'?','2025-12-02 17:32:36',0),(21,3,12,'Salve','2025-12-02 17:36:12',0),(22,12,3,'Bão demais fi','2025-12-02 17:36:26',0);
/*!40000 ALTER TABLE `mensagens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfil_aluno`
--

DROP TABLE IF EXISTS `perfil_aluno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perfil_aluno` (
  `usuario_id` int NOT NULL,
  `personal_id` int DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` enum('masculino','feminino','outro') DEFAULT NULL,
  `altura` decimal(3,2) DEFAULT NULL,
  `peso_atual` decimal(5,2) DEFAULT NULL,
  `objetivo` varchar(100) DEFAULT NULL,
  `observacoes_medicas` text,
  PRIMARY KEY (`usuario_id`),
  KEY `personal_id` (`personal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfil_aluno`
--

LOCK TABLES `perfil_aluno` WRITE;
/*!40000 ALTER TABLE `perfil_aluno` DISABLE KEYS */;
INSERT INTO `perfil_aluno` VALUES (10,1,'(11) 99999-0000','1995-05-20','masculino',1.80,82.50,'Hipertrofia','Sem lesões recentes.'),(11,2,'(21) 98888-1111','1990-08-15','feminino',1.65,68.00,'Emagrecimento','Condromalácia patelar leve.'),(12,3,'(31) 97777-2222','1988-12-01','masculino',1.75,90.00,'Condicionamento',NULL);
/*!40000 ALTER TABLE `perfil_aluno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perfil_personal`
--

DROP TABLE IF EXISTS `perfil_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perfil_personal` (
  `usuario_id` int NOT NULL,
  `cref` varchar(20) NOT NULL,
  `biografia` text,
  `cidade` varchar(50) DEFAULT NULL,
  `experiencia_anos` int DEFAULT NULL,
  `plano_assinatura` enum('avancado','premium','pro') DEFAULT 'avancado',
  `status_assinatura` enum('ativo','inativo','cancelado') DEFAULT 'ativo',
  PRIMARY KEY (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perfil_personal`
--

LOCK TABLES `perfil_personal` WRITE;
/*!40000 ALTER TABLE `perfil_personal` DISABLE KEYS */;
INSERT INTO `perfil_personal` VALUES (1,'123456-G/SP','Especialista em ganho de massa muscular. Treinos periodizados para resultados consistentes.','São Paulo, SP',8,'pro','ativo'),(2,'654321-G/RJ','Foco em saúde e perda de peso saudável através de treinos dinâmicos.','Rio de Janeiro, RJ',5,'premium','ativo'),(3,'987654-G/MG','Coach de Alta Performance. Supere seus limites com WODs intensos.','Belo Horizonte, MG',10,'avancado','ativo'),(4,'112233-G/RS','Equilíbrio entre corpo e mente. Aulas focadas em flexibilidade e respiração.','Porto Alegre, RS',6,'premium','ativo'),(13,'2',NULL,NULL,2,'avancado','ativo'),(14,'2',NULL,NULL,4,'avancado','ativo');
/*!40000 ALTER TABLE `perfil_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_especialidades`
--

DROP TABLE IF EXISTS `personal_especialidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_especialidades` (
  `personal_id` int NOT NULL,
  `especialidade_id` int NOT NULL,
  PRIMARY KEY (`personal_id`,`especialidade_id`),
  KEY `especialidade_id` (`especialidade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_especialidades`
--

LOCK TABLES `personal_especialidades` WRITE;
/*!40000 ALTER TABLE `personal_especialidades` DISABLE KEYS */;
INSERT INTO `personal_especialidades` VALUES (1,1),(2,2),(2,3),(3,3),(3,5),(4,4),(13,1),(13,2),(14,1),(14,2);
/*!40000 ALTER TABLE `personal_especialidades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `series_exercicio`
--

DROP TABLE IF EXISTS `series_exercicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `series_exercicio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exercicio_id` int NOT NULL,
  `numero_serie` int NOT NULL,
  `carga` decimal(5,2) DEFAULT NULL,
  `repeticoes` int DEFAULT NULL,
  `concluido` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `exercicio_id` (`exercicio_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series_exercicio`
--

LOCK TABLES `series_exercicio` WRITE;
/*!40000 ALTER TABLE `series_exercicio` DISABLE KEYS */;
INSERT INTO `series_exercicio` VALUES (1,1,1,20.00,12,0),(2,1,2,25.00,10,0),(3,1,3,30.00,8,0),(4,2,1,12.00,12,0),(5,2,2,12.00,12,0),(6,2,3,14.00,10,0),(7,3,1,15.00,15,0),(8,3,2,20.00,12,0),(9,3,3,25.00,10,0),(10,4,1,40.00,12,0),(11,4,2,45.00,10,0),(12,4,3,50.00,8,0),(13,5,1,30.00,12,0),(14,5,2,35.00,10,0),(15,5,3,40.00,8,0),(16,6,1,10.00,15,0),(17,6,2,12.00,12,0),(18,6,3,12.00,10,0),(19,9,1,30.00,12,0),(20,9,2,40.00,6,0),(21,9,3,40.00,6,0);
/*!40000 ALTER TABLE `series_exercicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('personal','aluno') NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Carlos Silva','carlos@befit.com','123456','personal',NULL,'2025-12-01 13:31:58'),(2,'Fernanda Costa','fernanda@befit.com','123456','personal',NULL,'2025-12-01 13:31:59'),(3,'Roberto Almeida','roberto@befit.com','123456','personal',NULL,'2025-12-01 13:31:59'),(4,'Juliana Martins','juliana@befit.com','123456','personal',NULL,'2025-12-01 13:31:59'),(10,'João Silva','joao@aluno.com','123456','aluno',NULL,'2025-12-01 13:31:59'),(11,'Maria Oliveira','maria@aluno.com','123456','aluno',NULL,'2025-12-01 13:31:59'),(12,'Pedro Santos','pedro@aluno.com','123456','aluno',NULL,'2025-12-01 13:31:59'),(13,'Gabriel Ramos','gabriel@gmail.com','123456','personal',NULL,'2025-12-02 17:47:14'),(14,'Samuel Ribas','samuel@befit.com','123456','personal',NULL,'2025-12-02 17:52:35');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-02 14:54:25
