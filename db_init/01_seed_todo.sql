--
-- PostgreSQL database dump
--

\restrict YrB9GkmHvuujLN9aXh94ymJ1EoU2rRmjWonNgkcCWRc21cDi8JwYOOMBhwpfy2r

-- Dumped from database version 16.13
-- Dumped by pg_dump version 16.13

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: metodo_enum; Type: TYPE; Schema: public; Owner: oko_admin
--

CREATE TYPE public.metodo_enum AS ENUM (
    'credencial',
    'QR',
    'Gafete'
);


ALTER TYPE public.metodo_enum OWNER TO oko_admin;

--
-- Name: resultado_enum; Type: TYPE; Schema: public; Owner: oko_admin
--

CREATE TYPE public.resultado_enum AS ENUM (
    'p',
    'd'
);


ALTER TYPE public.resultado_enum OWNER TO oko_admin;

--
-- Name: sexo_enum; Type: TYPE; Schema: public; Owner: oko_admin
--

CREATE TYPE public.sexo_enum AS ENUM (
    'H',
    'M'
);


ALTER TYPE public.sexo_enum OWNER TO oko_admin;

--
-- Name: tipo_acceso_enum; Type: TYPE; Schema: public; Owner: oko_admin
--

CREATE TYPE public.tipo_acceso_enum AS ENUM (
    'P',
    'V'
);


ALTER TYPE public.tipo_acceso_enum OWNER TO oko_admin;

--
-- Name: tipo_vehiculo_enum; Type: TYPE; Schema: public; Owner: oko_admin
--

CREATE TYPE public.tipo_vehiculo_enum AS ENUM (
    'auto',
    'moto',
    'camioneta',
    'otro'
);


ALTER TYPE public.tipo_vehiculo_enum OWNER TO oko_admin;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: accesos; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.accesos (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    id_vehiculo integer,
    id_puerta integer NOT NULL,
    id_dispositivo integer NOT NULL,
    fecha_hora timestamp without time zone,
    tipo_acceso public.tipo_acceso_enum NOT NULL,
    resultado public.resultado_enum NOT NULL,
    metodo public.metodo_enum NOT NULL,
    autoriza integer
);


ALTER TABLE public.accesos OWNER TO oko_admin;

--
-- Name: accesos_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.accesos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.accesos_id_seq OWNER TO oko_admin;

--
-- Name: accesos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.accesos_id_seq OWNED BY public.accesos.id;


--
-- Name: access_logs; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.access_logs (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    vehicle_plate character varying(255) NOT NULL,
    access_time timestamp(0) without time zone NOT NULL,
    access_type character varying(255) NOT NULL,
    is_authorized boolean NOT NULL
);


ALTER TABLE public.access_logs OWNER TO oko_admin;

--
-- Name: access_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.access_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.access_logs_id_seq OWNER TO oko_admin;

--
-- Name: access_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.access_logs_id_seq OWNED BY public.access_logs.id;


--
-- Name: alerts; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.alerts (
    id bigint NOT NULL,
    title character varying(255) NOT NULL,
    description text NOT NULL,
    severity character varying(255) NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    is_resolved boolean DEFAULT false NOT NULL
);


ALTER TABLE public.alerts OWNER TO oko_admin;

--
-- Name: alerts_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.alerts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.alerts_id_seq OWNER TO oko_admin;

--
-- Name: alerts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.alerts_id_seq OWNED BY public.alerts.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO oko_admin;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO oko_admin;

--
-- Name: carreras; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.carreras (
    id integer NOT NULL,
    nombre character varying(250) NOT NULL
);


ALTER TABLE public.carreras OWNER TO oko_admin;

--
-- Name: carreras_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.carreras_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.carreras_id_seq OWNER TO oko_admin;

--
-- Name: carreras_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.carreras_id_seq OWNED BY public.carreras.id;


--
-- Name: departamentos; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.departamentos (
    id integer NOT NULL,
    nombre character varying(250) NOT NULL
);


ALTER TABLE public.departamentos OWNER TO oko_admin;

--
-- Name: departamentos_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.departamentos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.departamentos_id_seq OWNER TO oko_admin;

--
-- Name: departamentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.departamentos_id_seq OWNED BY public.departamentos.id;


--
-- Name: dispositivos; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.dispositivos (
    id integer NOT NULL,
    nombre character varying(250) NOT NULL
);


ALTER TABLE public.dispositivos OWNER TO oko_admin;

--
-- Name: dispositivos_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.dispositivos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.dispositivos_id_seq OWNER TO oko_admin;

--
-- Name: dispositivos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.dispositivos_id_seq OWNED BY public.dispositivos.id;


--
-- Name: estatus; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.estatus (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL
);


ALTER TABLE public.estatus OWNER TO oko_admin;

--
-- Name: estatus_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.estatus_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estatus_id_seq OWNER TO oko_admin;

--
-- Name: estatus_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.estatus_id_seq OWNED BY public.estatus.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO oko_admin;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO oko_admin;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO oko_admin;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO oko_admin;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO oko_admin;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO oko_admin;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO oko_admin;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO oko_admin;

--
-- Name: permisos; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.permisos (
    id integer NOT NULL,
    id_usuario integer NOT NULL,
    estatus integer NOT NULL,
    emision date NOT NULL,
    vencimiento date NOT NULL
);


ALTER TABLE public.permisos OWNER TO oko_admin;

--
-- Name: permisos_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.permisos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permisos_id_seq OWNER TO oko_admin;

--
-- Name: permisos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.permisos_id_seq OWNED BY public.permisos.id;


--
-- Name: personas; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.personas (
    id integer NOT NULL,
    nombre character varying(200) NOT NULL,
    apellidos character varying(300) NOT NULL,
    fecha_nacimiento date,
    sexo public.sexo_enum,
    foto text,
    telefono character varying(10),
    mail character varying(50)
);


ALTER TABLE public.personas OWNER TO oko_admin;

--
-- Name: personas_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.personas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personas_id_seq OWNER TO oko_admin;

--
-- Name: personas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.personas_id_seq OWNED BY public.personas.id;


--
-- Name: personas_vehiculos; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.personas_vehiculos (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    id_vehiculo integer NOT NULL,
    id_estatus integer NOT NULL
);


ALTER TABLE public.personas_vehiculos OWNER TO oko_admin;

--
-- Name: personas_vehiculos_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.personas_vehiculos_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personas_vehiculos_id_seq OWNER TO oko_admin;

--
-- Name: personas_vehiculos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.personas_vehiculos_id_seq OWNED BY public.personas_vehiculos.id;


--
-- Name: puertas; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.puertas (
    id integer NOT NULL,
    nombre character varying(250) NOT NULL
);


ALTER TABLE public.puertas OWNER TO oko_admin;

--
-- Name: puertas_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.puertas_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.puertas_id_seq OWNER TO oko_admin;

--
-- Name: puertas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.puertas_id_seq OWNED BY public.puertas.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    nombre character varying(200) NOT NULL
);


ALTER TABLE public.roles OWNER TO oko_admin;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO oko_admin;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO oko_admin;

--
-- Name: users; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    role character varying(255) DEFAULT 'usuario'::character varying NOT NULL,
    telefono character varying(255),
    direccion character varying(255),
    activo boolean DEFAULT true NOT NULL,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'usuario'::character varying, 'visitante'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO oko_admin;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO oko_admin;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: usuarios; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.usuarios (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    id_carrera integer,
    id_departamento integer,
    id_rol integer NOT NULL,
    identificador character varying(15) NOT NULL,
    password character varying(255)
);


ALTER TABLE public.usuarios OWNER TO oko_admin;

--
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.usuarios_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO oko_admin;

--
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- Name: vehicles; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.vehicles (
    id bigint NOT NULL,
    plate character varying(255) NOT NULL,
    brand character varying(255) NOT NULL,
    model character varying(255) NOT NULL,
    color character varying(255) NOT NULL,
    owner_id bigint NOT NULL
);


ALTER TABLE public.vehicles OWNER TO oko_admin;

--
-- Name: vehicles_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.vehicles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicles_id_seq OWNER TO oko_admin;

--
-- Name: vehicles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.vehicles_id_seq OWNED BY public.vehicles.id;


--
-- Name: vehiculo; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.vehiculo (
    id integer NOT NULL,
    marca character varying(100) NOT NULL,
    modelo character varying(100) NOT NULL,
    anio integer,
    color character varying(50),
    tipo public.tipo_vehiculo_enum NOT NULL
);


ALTER TABLE public.vehiculo OWNER TO oko_admin;

--
-- Name: vehiculo_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.vehiculo_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehiculo_id_seq OWNER TO oko_admin;

--
-- Name: vehiculo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.vehiculo_id_seq OWNED BY public.vehiculo.id;


--
-- Name: visitantes; Type: TABLE; Schema: public; Owner: oko_admin
--

CREATE TABLE public.visitantes (
    id integer NOT NULL,
    id_persona integer NOT NULL,
    identificacion character varying(500),
    motivo_visita character varying(600),
    id_departamento integer NOT NULL,
    iden_temp character varying(15)
);


ALTER TABLE public.visitantes OWNER TO oko_admin;

--
-- Name: visitantes_id_seq; Type: SEQUENCE; Schema: public; Owner: oko_admin
--

CREATE SEQUENCE public.visitantes_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.visitantes_id_seq OWNER TO oko_admin;

--
-- Name: visitantes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: oko_admin
--

ALTER SEQUENCE public.visitantes_id_seq OWNED BY public.visitantes.id;


--
-- Name: accesos id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.accesos ALTER COLUMN id SET DEFAULT nextval('public.accesos_id_seq'::regclass);


--
-- Name: access_logs id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.access_logs ALTER COLUMN id SET DEFAULT nextval('public.access_logs_id_seq'::regclass);


--
-- Name: alerts id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.alerts ALTER COLUMN id SET DEFAULT nextval('public.alerts_id_seq'::regclass);


--
-- Name: carreras id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.carreras ALTER COLUMN id SET DEFAULT nextval('public.carreras_id_seq'::regclass);


--
-- Name: departamentos id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.departamentos ALTER COLUMN id SET DEFAULT nextval('public.departamentos_id_seq'::regclass);


--
-- Name: dispositivos id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.dispositivos ALTER COLUMN id SET DEFAULT nextval('public.dispositivos_id_seq'::regclass);


--
-- Name: estatus id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.estatus ALTER COLUMN id SET DEFAULT nextval('public.estatus_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permisos id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.permisos ALTER COLUMN id SET DEFAULT nextval('public.permisos_id_seq'::regclass);


--
-- Name: personas id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas ALTER COLUMN id SET DEFAULT nextval('public.personas_id_seq'::regclass);


--
-- Name: personas_vehiculos id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas_vehiculos ALTER COLUMN id SET DEFAULT nextval('public.personas_vehiculos_id_seq'::regclass);


--
-- Name: puertas id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.puertas ALTER COLUMN id SET DEFAULT nextval('public.puertas_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- Name: vehicles id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.vehicles ALTER COLUMN id SET DEFAULT nextval('public.vehicles_id_seq'::regclass);


--
-- Name: vehiculo id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.vehiculo ALTER COLUMN id SET DEFAULT nextval('public.vehiculo_id_seq'::regclass);


--
-- Name: visitantes id; Type: DEFAULT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.visitantes ALTER COLUMN id SET DEFAULT nextval('public.visitantes_id_seq'::regclass);


--
-- Data for Name: accesos; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.accesos VALUES (1, 2, 1, 1, 1, '2026-03-29 02:43:36.119845', 'P', 'p', 'credencial', NULL);
INSERT INTO public.accesos VALUES (2, 3, 2, 1, 1, '2026-03-29 02:43:36.119847', 'V', 'p', 'credencial', NULL);


--
-- Data for Name: access_logs; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: alerts; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: carreras; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: departamentos; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: dispositivos; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.dispositivos VALUES (1, 'Cámara 1');


--
-- Data for Name: estatus; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.estatus VALUES (1, 'Activo');
INSERT INTO public.estatus VALUES (2, 'Inactivo');
INSERT INTO public.estatus VALUES (3, 'Suspendido');


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.migrations VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations VALUES (4, '2024_03_27_000001_create_vehicles_table', 1);
INSERT INTO public.migrations VALUES (5, '2024_03_27_000002_create_access_logs_table', 1);
INSERT INTO public.migrations VALUES (6, '2024_03_27_000003_create_alerts_table', 1);
INSERT INTO public.migrations VALUES (7, '2024_03_28_000001_add_fields_to_users_table', 1);


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: permisos; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: personas; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.personas VALUES (1, 'Admin', 'Oko', NULL, NULL, NULL, '1234567890', 'admin@okovision.com');
INSERT INTO public.personas VALUES (4, 'eros', 'cano', NULL, NULL, NULL, NULL, 'cano@okovision.com');
INSERT INTO public.personas VALUES (5, 'axel', 'horta', NULL, NULL, NULL, '4191310452', 'axe@okovision.com');
INSERT INTO public.personas VALUES (6, 'carmen', 'garcia', NULL, NULL, NULL, '1234567891', 'car@okovision.com');
INSERT INTO public.personas VALUES (3, 'Maria', 'Garcia', NULL, NULL, NULL, NULL, 'maria@outlook.com');
INSERT INTO public.personas VALUES (2, 'Juan', 'Perez', NULL, NULL, NULL, NULL, 'juan@gmail.com');
INSERT INTO public.personas VALUES (7, 'test', 'test', NULL, NULL, NULL, '1234567890', 'test@okovision.com');
INSERT INTO public.personas VALUES (8, 'test', 'tester', NULL, NULL, NULL, '1234567890', 'test_us@okovision.com');
INSERT INTO public.personas VALUES (9, 'axelito', 'pereZ', '2004-03-11', 'M', '/storage/fotos/x5SjRIbLJPoTTxPrmMGpIayGwFEyb7woBsB4Fbbc.png', NULL, 'axel@okovision.com');


--
-- Data for Name: personas_vehiculos; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.personas_vehiculos VALUES (1, 2, 1, 1);
INSERT INTO public.personas_vehiculos VALUES (2, 3, 2, 1);
INSERT INTO public.personas_vehiculos VALUES (3, 1, 3, 1);


--
-- Data for Name: puertas; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.puertas VALUES (1, 'Entrada Principal');


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.roles VALUES (1, 'Administrador');
INSERT INTO public.roles VALUES (2, 'Estudiante');
INSERT INTO public.roles VALUES (3, 'Docente');
INSERT INTO public.roles VALUES (4, 'Personal Administrativo');


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.usuarios VALUES (1, 1, NULL, NULL, 1, 'admin', '$2b$12$OL/FdJUUNgBKh52K5JpKN.6699YP0g7v0fkl/bOkfqG2Ur3OLtLpq');
INSERT INTO public.usuarios VALUES (2, 2, NULL, NULL, 2, 'juan_perez', '$2b$12$eD/vqYSUDGM3bJW8UkUCCOO9wSta4bivBkc6AYqQXNgh0sYIIx.S6');
INSERT INTO public.usuarios VALUES (3, 3, NULL, NULL, 2, 'maria_garcia', '$2b$12$94JbaZwWwGLRGb.cgaVFHOIK58JsN4NjRpkrNz2P.ciw8Nji2Lx/.');
INSERT INTO public.usuarios VALUES (6, 6, NULL, NULL, 1, 'car_g', '$2b$12$gAw9dvq4wY8EyohY2p2ak.gN5f9UgSEKeU1npt6qzzSK.yv1i.086');
INSERT INTO public.usuarios VALUES (7, 7, NULL, NULL, 3, 'oko_test', '$2b$12$.Myd73s2Mke6jDzLVqR01OHIkdCaDHlpqr1Jq77cE7AUqFLKjnZG6');
INSERT INTO public.usuarios VALUES (8, 8, NULL, NULL, 2, 'oko_us', '$2b$12$HPy4YsJo8qlHFCcCtZjFyecdG.sKjxx4SEF.6B53ciMb5oqbzszrm');
INSERT INTO public.usuarios VALUES (9, 9, NULL, NULL, 2, '123047063', '$2b$12$4EyGrGrGY/LyhLpZgavZjePqmdxQMQolyvoRKKXmoBp1aMBDd67CK');


--
-- Data for Name: vehicles; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Data for Name: vehiculo; Type: TABLE DATA; Schema: public; Owner: oko_admin
--

INSERT INTO public.vehiculo VALUES (1, 'Toyota', 'Corolla', 2022, 'Gris', 'auto');
INSERT INTO public.vehiculo VALUES (2, 'Honda', 'Civic', 2021, 'Blanco', 'auto');
INSERT INTO public.vehiculo VALUES (3, 'Tesla', 'Model 3', 2023, 'Negro', 'auto');


--
-- Data for Name: visitantes; Type: TABLE DATA; Schema: public; Owner: oko_admin
--



--
-- Name: accesos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.accesos_id_seq', 2, true);


--
-- Name: access_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.access_logs_id_seq', 1, false);


--
-- Name: alerts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.alerts_id_seq', 1, false);


--
-- Name: carreras_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.carreras_id_seq', 1, false);


--
-- Name: departamentos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.departamentos_id_seq', 1, false);


--
-- Name: dispositivos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.dispositivos_id_seq', 1, true);


--
-- Name: estatus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.estatus_id_seq', 3, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.migrations_id_seq', 7, true);


--
-- Name: permisos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.permisos_id_seq', 1, false);


--
-- Name: personas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.personas_id_seq', 9, true);


--
-- Name: personas_vehiculos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.personas_vehiculos_id_seq', 3, true);


--
-- Name: puertas_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.puertas_id_seq', 1, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.users_id_seq', 1, false);


--
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 9, true);


--
-- Name: vehicles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.vehicles_id_seq', 1, false);


--
-- Name: vehiculo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.vehiculo_id_seq', 3, true);


--
-- Name: visitantes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: oko_admin
--

SELECT pg_catalog.setval('public.visitantes_id_seq', 1, false);


--
-- Name: accesos accesos_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.accesos
    ADD CONSTRAINT accesos_pkey PRIMARY KEY (id);


--
-- Name: access_logs access_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.access_logs
    ADD CONSTRAINT access_logs_pkey PRIMARY KEY (id);


--
-- Name: alerts alerts_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.alerts
    ADD CONSTRAINT alerts_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: carreras carreras_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.carreras
    ADD CONSTRAINT carreras_pkey PRIMARY KEY (id);


--
-- Name: departamentos departamentos_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.departamentos
    ADD CONSTRAINT departamentos_pkey PRIMARY KEY (id);


--
-- Name: dispositivos dispositivos_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.dispositivos
    ADD CONSTRAINT dispositivos_pkey PRIMARY KEY (id);


--
-- Name: estatus estatus_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.estatus
    ADD CONSTRAINT estatus_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permisos permisos_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT permisos_pkey PRIMARY KEY (id);


--
-- Name: personas personas_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_pkey PRIMARY KEY (id);


--
-- Name: personas_vehiculos personas_vehiculos_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas_vehiculos
    ADD CONSTRAINT personas_vehiculos_pkey PRIMARY KEY (id);


--
-- Name: puertas puertas_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.puertas
    ADD CONSTRAINT puertas_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_pkey PRIMARY KEY (id);


--
-- Name: vehicles vehicles_plate_unique; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_plate_unique UNIQUE (plate);


--
-- Name: vehiculo vehiculo_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.vehiculo
    ADD CONSTRAINT vehiculo_pkey PRIMARY KEY (id);


--
-- Name: visitantes visitantes_pkey; Type: CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.visitantes
    ADD CONSTRAINT visitantes_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: ix_accesos_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_accesos_id ON public.accesos USING btree (id);


--
-- Name: ix_carreras_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_carreras_id ON public.carreras USING btree (id);


--
-- Name: ix_departamentos_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_departamentos_id ON public.departamentos USING btree (id);


--
-- Name: ix_dispositivos_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_dispositivos_id ON public.dispositivos USING btree (id);


--
-- Name: ix_estatus_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_estatus_id ON public.estatus USING btree (id);


--
-- Name: ix_permisos_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_permisos_id ON public.permisos USING btree (id);


--
-- Name: ix_personas_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_personas_id ON public.personas USING btree (id);


--
-- Name: ix_personas_vehiculos_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_personas_vehiculos_id ON public.personas_vehiculos USING btree (id);


--
-- Name: ix_puertas_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_puertas_id ON public.puertas USING btree (id);


--
-- Name: ix_roles_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_roles_id ON public.roles USING btree (id);


--
-- Name: ix_usuarios_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_usuarios_id ON public.usuarios USING btree (id);


--
-- Name: ix_vehiculo_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_vehiculo_id ON public.vehiculo USING btree (id);


--
-- Name: ix_visitantes_id; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX ix_visitantes_id ON public.visitantes USING btree (id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: oko_admin
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: accesos accesos_id_dispositivo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.accesos
    ADD CONSTRAINT accesos_id_dispositivo_fkey FOREIGN KEY (id_dispositivo) REFERENCES public.dispositivos(id);


--
-- Name: accesos accesos_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.accesos
    ADD CONSTRAINT accesos_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id);


--
-- Name: accesos accesos_id_puerta_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.accesos
    ADD CONSTRAINT accesos_id_puerta_fkey FOREIGN KEY (id_puerta) REFERENCES public.puertas(id);


--
-- Name: accesos accesos_id_vehiculo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.accesos
    ADD CONSTRAINT accesos_id_vehiculo_fkey FOREIGN KEY (id_vehiculo) REFERENCES public.vehiculo(id);


--
-- Name: access_logs access_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.access_logs
    ADD CONSTRAINT access_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: permisos permisos_id_usuario_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.permisos
    ADD CONSTRAINT permisos_id_usuario_fkey FOREIGN KEY (id_usuario) REFERENCES public.usuarios(id);


--
-- Name: personas_vehiculos personas_vehiculos_id_estatus_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas_vehiculos
    ADD CONSTRAINT personas_vehiculos_id_estatus_fkey FOREIGN KEY (id_estatus) REFERENCES public.estatus(id);


--
-- Name: personas_vehiculos personas_vehiculos_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas_vehiculos
    ADD CONSTRAINT personas_vehiculos_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id);


--
-- Name: personas_vehiculos personas_vehiculos_id_vehiculo_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.personas_vehiculos
    ADD CONSTRAINT personas_vehiculos_id_vehiculo_fkey FOREIGN KEY (id_vehiculo) REFERENCES public.vehiculo(id);


--
-- Name: usuarios usuarios_id_carrera_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_id_carrera_fkey FOREIGN KEY (id_carrera) REFERENCES public.carreras(id);


--
-- Name: usuarios usuarios_id_departamento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_id_departamento_fkey FOREIGN KEY (id_departamento) REFERENCES public.departamentos(id);


--
-- Name: usuarios usuarios_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id);


--
-- Name: usuarios usuarios_id_rol_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_id_rol_fkey FOREIGN KEY (id_rol) REFERENCES public.roles(id);


--
-- Name: vehicles vehicles_owner_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.vehicles
    ADD CONSTRAINT vehicles_owner_id_foreign FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: visitantes visitantes_id_departamento_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.visitantes
    ADD CONSTRAINT visitantes_id_departamento_fkey FOREIGN KEY (id_departamento) REFERENCES public.departamentos(id);


--
-- Name: visitantes visitantes_id_persona_fkey; Type: FK CONSTRAINT; Schema: public; Owner: oko_admin
--

ALTER TABLE ONLY public.visitantes
    ADD CONSTRAINT visitantes_id_persona_fkey FOREIGN KEY (id_persona) REFERENCES public.personas(id);


--
-- PostgreSQL database dump complete
--

\unrestrict YrB9GkmHvuujLN9aXh94ymJ1EoU2rRmjWonNgkcCWRc21cDi8JwYOOMBhwpfy2r

