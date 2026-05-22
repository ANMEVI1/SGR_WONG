
CREATE TABLE Asistencia
( 
	EmpleadoID           char(18)  NOT NULL ,
	AsistenciaID         char(18)  NOT NULL ,
	Fecha                char(18)  NULL ,
	Hora_Entrada         char(18)  NULL ,
	Hora_Salida          char(18)  NULL ,
	Hora_Extra           char(18)  NULL 
)
go



ALTER TABLE Asistencia
	ADD CONSTRAINT XPKAsistencia PRIMARY KEY  CLUSTERED (AsistenciaID ASC)
go



CREATE TABLE Caja
( 
	CajaID               char(18)  NOT NULL ,
	Monto_Apertura       char(18)  NULL ,
	Monto_Cierre         char(18)  NULL ,
	Fecha_hora_Apertura  char(18)  NULL ,
	Fecha_hora_Cierre    char(18)  NULL ,
	Estado               char(18)  NULL 
)
go



ALTER TABLE Caja
	ADD CONSTRAINT XPKCaja PRIMARY KEY  CLUSTERED (CajaID ASC)
go



CREATE TABLE Categoria
( 
	CatID                char(18)  NOT NULL ,
	Nombre               char(18)  NULL ,
	Descripcion          char(18)  NULL 
)
go



ALTER TABLE Categoria
	ADD CONSTRAINT XPKCategoria PRIMARY KEY  CLUSTERED (CatID ASC)
go



CREATE TABLE Cliente
( 
	ClienteID            char(8)  NOT NULL ,
	Nombre_Apellidos     char(18)  NULL ,
	DNI                  char(8)  NOT NULL ,
	Correo               varchar(50)  NOT NULL ,
	Contraseña           char(24)  NOT NULL ,
	Telefono             char(8)  NOT NULL ,
	Direcion             char(18)  NULL ,
	Fecha_Creacion       char(18)  NULL 
)
go



ALTER TABLE Cliente
	ADD CONSTRAINT XPKCliente PRIMARY KEY  CLUSTERED (ClienteID ASC)
go



CREATE TABLE Comprobante_Pago
( 
	PedidoID             char(18)  NOT NULL ,
	ComPagID             char(18)  NOT NULL ,
	Tipo_Comprobante     char(18)  NULL ,
	Numero_Serie         char(18)  NULL ,
	IGV                  char(18)  NULL ,
	Total                char(18)  NULL ,
	Fecha_Emision        char(18)  NULL 
)
go



ALTER TABLE Comprobante_Pago
	ADD CONSTRAINT XPKComprobante_Pago PRIMARY KEY  CLUSTERED (ComPagID ASC)
go



CREATE TABLE Detalle_Pedido
( 
	DetPedID             char(18)  NOT NULL ,
	PedidoID             char(18)  NOT NULL ,
	Producto             char(18)  NULL ,
	Cantidad             char(18)  NULL ,
	Precio_Unitario      char(18)  NULL ,
	Subtotal             char(18)  NULL 
)
go



ALTER TABLE Detalle_Pedido
	ADD CONSTRAINT XPKDetalle_Pedido PRIMARY KEY  CLUSTERED (DetPedID ASC)
go



CREATE TABLE Empleado
( 
	EmpleadoID           char(18)  NOT NULL ,
	RolID                char(2)  NOT NULL ,
	TurnoID              char(18)  NOT NULL ,
	Nombre_Apellidos     char(18)  NULL ,
	DNI                  char(18)  NULL ,
	Telefono             char(18)  NULL ,
	Sueldo               char(18)  NULL ,
	Estado               char(18)  NULL 
)
go



ALTER TABLE Empleado
	ADD CONSTRAINT XPKEmpleado PRIMARY KEY  CLUSTERED (EmpleadoID ASC)
go



CREATE TABLE Kardex
( 
	ProductoID           char(18)  NOT NULL ,
	ProveedorID          char(18)  NOT NULL ,
	NumMov               char(18)  NOT NULL ,
	tipo_movimiento      char(18)  NULL ,
	Cantidad             char(18)  NULL ,
	fecha                char(18)  NULL ,
	Motivo               char(18)  NULL ,
	Lote                 char(18)  NULL 
)
go



ALTER TABLE Kardex
	ADD CONSTRAINT XPKKardex PRIMARY KEY  CLUSTERED (NumMov ASC)
go



CREATE TABLE Mesa
( 
	MesaID               char(18)  NOT NULL ,
	Numero_Mesa          char(18)  NULL ,
	Capacidad            char(18)  NULL ,
	Estado               char(18)  NULL ,
	PedidoID             char(18)  NOT NULL 
)
go



ALTER TABLE Mesa
	ADD CONSTRAINT XPKMesa PRIMARY KEY  CLUSTERED (MesaID ASC)
go



CREATE TABLE Metodo_Pago
( 
	MetPagID             char(18)  NOT NULL ,
	ComPagID             char(18)  NOT NULL ,
	Nombre               char(18)  NULL ,
	Estado               char(18)  NULL 
)
go



ALTER TABLE Metodo_Pago
	ADD CONSTRAINT XPKMetodo_Pago PRIMARY KEY  CLUSTERED (MetPagID ASC)
go



CREATE TABLE Pedido
( 
	ClienteID            char(8)  NOT NULL ,
	PedidoID             char(18)  NOT NULL ,
	Fecha_Hora           char(18)  NULL ,
	Observaciones        char(18)  NULL ,
	Tipo_Pedido          char(18)  NULL 
)
go



ALTER TABLE Pedido
	ADD CONSTRAINT XPKPedido PRIMARY KEY  CLUSTERED (PedidoID ASC)
go



CREATE TABLE Producto
( 
	ProductoID           char(18)  NOT NULL ,
	Nombre               char(18)  NULL ,
	Descripcion          char(18)  NULL ,
	Precio_Venta         char(18)  NULL ,
	CatID                char(18)  NOT NULL ,
	Precio_Costo         char(18)  NULL ,
	Tipo                 char(18)  NULL ,
	Estado               char(18)  NULL 
)
go



ALTER TABLE Producto
	ADD CONSTRAINT XPKProducto PRIMARY KEY  CLUSTERED (ProductoID ASC)
go



CREATE TABLE Proveedor
( 
	ProveedorID          char(18)  NOT NULL ,
	Razon_Social         char(18)  NULL ,
	Ruc                  char(18)  NULL ,
	Contacto             char(18)  NULL ,
	Telefono             char(18)  NULL ,
	Direcion             char(18)  NULL ,
	Tipo_Insumo          char(18)  NULL 
)
go



ALTER TABLE Proveedor
	ADD CONSTRAINT XPKProveedor PRIMARY KEY  CLUSTERED (ProveedorID ASC)
go



CREATE TABLE Tipo_Rol
( 
	RolID                char(2)  NOT NULL ,
	Descripcion          varchar(50)  NOT NULL 
)
go



ALTER TABLE Tipo_Rol
	ADD CONSTRAINT XPKTipo_Rol PRIMARY KEY  CLUSTERED (RolID ASC)
go



CREATE TABLE Tipo_Usuario
( 
	TipUsuID             char(2)  NOT NULL ,
	Descripcion          varchar(18)  NOT NULL 
)
go



ALTER TABLE Tipo_Usuario
	ADD CONSTRAINT XPKTipo_Usuario PRIMARY KEY  CLUSTERED (TipUsuID ASC)
go



CREATE TABLE Turno
( 
	TurnoID              char(18)  NOT NULL ,
	Descripcion          char(18)  NULL ,
	Hora_Incio           char(18)  NULL ,
	Hora_Fin             char(18)  NULL 
)
go



ALTER TABLE Turno
	ADD CONSTRAINT XPKTurno PRIMARY KEY  CLUSTERED (TurnoID ASC)
go



CREATE TABLE Usuario
( 
	TipUsuID             char(2)  NOT NULL ,
	UsuarioID            char(18)  NOT NULL ,
	Nombre_Apellidos     varchar(50)  NOT NULL ,
	Correo               char(18)  NULL ,
	Contraseña           char(18)  NULL ,
	DNI                  char(18)  NULL 
)
go



ALTER TABLE Usuario
	ADD CONSTRAINT XPKUsuario PRIMARY KEY  CLUSTERED (UsuarioID ASC)
go




ALTER TABLE Asistencia
	ADD CONSTRAINT R_7 FOREIGN KEY (EmpleadoID) REFERENCES Empleado(EmpleadoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Comprobante_Pago
	ADD CONSTRAINT R_13 FOREIGN KEY (PedidoID) REFERENCES Pedido(PedidoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Comprobante_Pago
	ADD CONSTRAINT R_21 FOREIGN KEY (PedidoID) REFERENCES Pedido(PedidoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Detalle_Pedido
	ADD CONSTRAINT R_20 FOREIGN KEY (PedidoID) REFERENCES Pedido(PedidoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Empleado
	ADD CONSTRAINT R_4 FOREIGN KEY (RolID) REFERENCES Tipo_Rol(RolID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Empleado
	ADD CONSTRAINT R_6 FOREIGN KEY (TurnoID) REFERENCES Turno(TurnoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Kardex
	ADD CONSTRAINT R_11 FOREIGN KEY (ProductoID) REFERENCES Producto(ProductoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Kardex
	ADD CONSTRAINT R_16 FOREIGN KEY (ProveedorID) REFERENCES Proveedor(ProveedorID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Mesa
	ADD CONSTRAINT R_15 FOREIGN KEY (PedidoID) REFERENCES Pedido(PedidoID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Metodo_Pago
	ADD CONSTRAINT R_14 FOREIGN KEY (ComPagID) REFERENCES Comprobante_Pago(ComPagID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Pedido
	ADD CONSTRAINT R_9 FOREIGN KEY (ClienteID) REFERENCES Cliente(ClienteID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Producto
	ADD CONSTRAINT R_19 FOREIGN KEY (CatID) REFERENCES Categoria(CatID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




ALTER TABLE Usuario
	ADD CONSTRAINT R_18 FOREIGN KEY (TipUsuID) REFERENCES Tipo_Usuario(TipUsuID)
		ON DELETE NO ACTION
		ON UPDATE NO ACTION
go




CREATE TRIGGER tD_Asistencia ON Asistencia FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Asistencia */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Empleado  Asistencia on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="0001321f", PARENT_OWNER="", PARENT_TABLE="Empleado"
    CHILD_OWNER="", CHILD_TABLE="Asistencia"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_7", FK_COLUMNS="EmpleadoID" */
    IF EXISTS (SELECT * FROM deleted,Empleado
      WHERE
        /* %JoinFKPK(deleted,Empleado," = "," AND") */
        deleted.EmpleadoID = Empleado.EmpleadoID AND
        NOT EXISTS (
          SELECT * FROM Asistencia
          WHERE
            /* %JoinFKPK(Asistencia,Empleado," = "," AND") */
            Asistencia.EmpleadoID = Empleado.EmpleadoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Asistencia because Empleado exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Asistencia ON Asistencia FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Asistencia */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insAsistenciaID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Empleado  Asistencia on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00015037", PARENT_OWNER="", PARENT_TABLE="Empleado"
    CHILD_OWNER="", CHILD_TABLE="Asistencia"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_7", FK_COLUMNS="EmpleadoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(EmpleadoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Empleado
        WHERE
          /* %JoinFKPK(inserted,Empleado) */
          inserted.EmpleadoID = Empleado.EmpleadoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Asistencia because Empleado does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Categoria ON Categoria FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Categoria */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Categoria  Producto on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0000ef1b", PARENT_OWNER="", PARENT_TABLE="Categoria"
    CHILD_OWNER="", CHILD_TABLE="Producto"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_19", FK_COLUMNS="CatID" */
    IF EXISTS (
      SELECT * FROM deleted,Producto
      WHERE
        /*  %JoinFKPK(Producto,deleted," = "," AND") */
        Producto.CatID = deleted.CatID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Categoria because Producto exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Categoria ON Categoria FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Categoria */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insCatID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Categoria  Producto on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="0001089c", PARENT_OWNER="", PARENT_TABLE="Categoria"
    CHILD_OWNER="", CHILD_TABLE="Producto"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_19", FK_COLUMNS="CatID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(CatID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Producto
      WHERE
        /*  %JoinFKPK(Producto,deleted," = "," AND") */
        Producto.CatID = deleted.CatID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Categoria because Producto exists.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Cliente ON Cliente FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Cliente */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Cliente  Pedido on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0000e3d5", PARENT_OWNER="", PARENT_TABLE="Cliente"
    CHILD_OWNER="", CHILD_TABLE="Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_9", FK_COLUMNS="ClienteID" */
    IF EXISTS (
      SELECT * FROM deleted,Pedido
      WHERE
        /*  %JoinFKPK(Pedido,deleted," = "," AND") */
        Pedido.ClienteID = deleted.ClienteID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Cliente because Pedido exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Cliente ON Cliente FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Cliente */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insClienteID char(8),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Cliente  Pedido on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="0000fb8d", PARENT_OWNER="", PARENT_TABLE="Cliente"
    CHILD_OWNER="", CHILD_TABLE="Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_9", FK_COLUMNS="ClienteID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(ClienteID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Pedido
      WHERE
        /*  %JoinFKPK(Pedido,deleted," = "," AND") */
        Pedido.ClienteID = deleted.ClienteID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Cliente because Pedido exists.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Comprobante_Pago ON Comprobante_Pago FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Comprobante_Pago */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Comprobante_Pago  Metodo_Pago on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="000357de", PARENT_OWNER="", PARENT_TABLE="Comprobante_Pago"
    CHILD_OWNER="", CHILD_TABLE="Metodo_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_14", FK_COLUMNS="ComPagID" */
    IF EXISTS (
      SELECT * FROM deleted,Metodo_Pago
      WHERE
        /*  %JoinFKPK(Metodo_Pago,deleted," = "," AND") */
        Metodo_Pago.ComPagID = deleted.ComPagID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Comprobante_Pago because Metodo_Pago exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Pedido  Comprobante_Pago on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_13", FK_COLUMNS="PedidoID" */
    IF EXISTS (SELECT * FROM deleted,Pedido
      WHERE
        /* %JoinFKPK(deleted,Pedido," = "," AND") */
        deleted.PedidoID = Pedido.PedidoID AND
        NOT EXISTS (
          SELECT * FROM Comprobante_Pago
          WHERE
            /* %JoinFKPK(Comprobante_Pago,Pedido," = "," AND") */
            Comprobante_Pago.PedidoID = Pedido.PedidoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Comprobante_Pago because Pedido exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Pedido  Comprobante_Pago on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_21", FK_COLUMNS="PedidoID" */
    IF EXISTS (SELECT * FROM deleted,Pedido
      WHERE
        /* %JoinFKPK(deleted,Pedido," = "," AND") */
        deleted.PedidoID = Pedido.PedidoID AND
        NOT EXISTS (
          SELECT * FROM Comprobante_Pago
          WHERE
            /* %JoinFKPK(Comprobante_Pago,Pedido," = "," AND") */
            Comprobante_Pago.PedidoID = Pedido.PedidoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Comprobante_Pago because Pedido exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Comprobante_Pago ON Comprobante_Pago FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Comprobante_Pago */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insComPagID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Comprobante_Pago  Metodo_Pago on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="0003a085", PARENT_OWNER="", PARENT_TABLE="Comprobante_Pago"
    CHILD_OWNER="", CHILD_TABLE="Metodo_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_14", FK_COLUMNS="ComPagID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(ComPagID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Metodo_Pago
      WHERE
        /*  %JoinFKPK(Metodo_Pago,deleted," = "," AND") */
        Metodo_Pago.ComPagID = deleted.ComPagID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Comprobante_Pago because Metodo_Pago exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Pedido  Comprobante_Pago on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_13", FK_COLUMNS="PedidoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Pedido
        WHERE
          /* %JoinFKPK(inserted,Pedido) */
          inserted.PedidoID = Pedido.PedidoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Comprobante_Pago because Pedido does not exist.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Pedido  Comprobante_Pago on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_21", FK_COLUMNS="PedidoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Pedido
        WHERE
          /* %JoinFKPK(inserted,Pedido) */
          inserted.PedidoID = Pedido.PedidoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Comprobante_Pago because Pedido does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Detalle_Pedido ON Detalle_Pedido FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Detalle_Pedido */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Pedido  Detalle_Pedido on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="000126be", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Detalle_Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_20", FK_COLUMNS="PedidoID" */
    IF EXISTS (SELECT * FROM deleted,Pedido
      WHERE
        /* %JoinFKPK(deleted,Pedido," = "," AND") */
        deleted.PedidoID = Pedido.PedidoID AND
        NOT EXISTS (
          SELECT * FROM Detalle_Pedido
          WHERE
            /* %JoinFKPK(Detalle_Pedido,Pedido," = "," AND") */
            Detalle_Pedido.PedidoID = Pedido.PedidoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Detalle_Pedido because Pedido exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Detalle_Pedido ON Detalle_Pedido FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Detalle_Pedido */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insDetPedID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Pedido  Detalle_Pedido on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00014f5a", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Detalle_Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_20", FK_COLUMNS="PedidoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Pedido
        WHERE
          /* %JoinFKPK(inserted,Pedido) */
          inserted.PedidoID = Pedido.PedidoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Detalle_Pedido because Pedido does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Empleado ON Empleado FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Empleado */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Empleado  Asistencia on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="00030b0c", PARENT_OWNER="", PARENT_TABLE="Empleado"
    CHILD_OWNER="", CHILD_TABLE="Asistencia"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_7", FK_COLUMNS="EmpleadoID" */
    IF EXISTS (
      SELECT * FROM deleted,Asistencia
      WHERE
        /*  %JoinFKPK(Asistencia,deleted," = "," AND") */
        Asistencia.EmpleadoID = deleted.EmpleadoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Empleado because Asistencia exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Tipo_Rol  Empleado on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Tipo_Rol"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_4", FK_COLUMNS="RolID" */
    IF EXISTS (SELECT * FROM deleted,Tipo_Rol
      WHERE
        /* %JoinFKPK(deleted,Tipo_Rol," = "," AND") */
        deleted.RolID = Tipo_Rol.RolID AND
        NOT EXISTS (
          SELECT * FROM Empleado
          WHERE
            /* %JoinFKPK(Empleado,Tipo_Rol," = "," AND") */
            Empleado.RolID = Tipo_Rol.RolID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Empleado because Tipo_Rol exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Turno  Empleado on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Turno"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_6", FK_COLUMNS="TurnoID" */
    IF EXISTS (SELECT * FROM deleted,Turno
      WHERE
        /* %JoinFKPK(deleted,Turno," = "," AND") */
        deleted.TurnoID = Turno.TurnoID AND
        NOT EXISTS (
          SELECT * FROM Empleado
          WHERE
            /* %JoinFKPK(Empleado,Turno," = "," AND") */
            Empleado.TurnoID = Turno.TurnoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Empleado because Turno exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Empleado ON Empleado FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Empleado */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insEmpleadoID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Empleado  Asistencia on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="00037c7a", PARENT_OWNER="", PARENT_TABLE="Empleado"
    CHILD_OWNER="", CHILD_TABLE="Asistencia"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_7", FK_COLUMNS="EmpleadoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(EmpleadoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Asistencia
      WHERE
        /*  %JoinFKPK(Asistencia,deleted," = "," AND") */
        Asistencia.EmpleadoID = deleted.EmpleadoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Empleado because Asistencia exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Tipo_Rol  Empleado on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Tipo_Rol"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_4", FK_COLUMNS="RolID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(RolID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Tipo_Rol
        WHERE
          /* %JoinFKPK(inserted,Tipo_Rol) */
          inserted.RolID = Tipo_Rol.RolID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Empleado because Tipo_Rol does not exist.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Turno  Empleado on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Turno"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_6", FK_COLUMNS="TurnoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(TurnoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Turno
        WHERE
          /* %JoinFKPK(inserted,Turno) */
          inserted.TurnoID = Turno.TurnoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Empleado because Turno does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Kardex ON Kardex FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Kardex */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Producto  Kardex on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00024420", PARENT_OWNER="", PARENT_TABLE="Producto"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_11", FK_COLUMNS="ProductoID" */
    IF EXISTS (SELECT * FROM deleted,Producto
      WHERE
        /* %JoinFKPK(deleted,Producto," = "," AND") */
        deleted.ProductoID = Producto.ProductoID AND
        NOT EXISTS (
          SELECT * FROM Kardex
          WHERE
            /* %JoinFKPK(Kardex,Producto," = "," AND") */
            Kardex.ProductoID = Producto.ProductoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Kardex because Producto exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Proveedor  Kardex on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Proveedor"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_16", FK_COLUMNS="ProveedorID" */
    IF EXISTS (SELECT * FROM deleted,Proveedor
      WHERE
        /* %JoinFKPK(deleted,Proveedor," = "," AND") */
        deleted.ProveedorID = Proveedor.ProveedorID AND
        NOT EXISTS (
          SELECT * FROM Kardex
          WHERE
            /* %JoinFKPK(Kardex,Proveedor," = "," AND") */
            Kardex.ProveedorID = Proveedor.ProveedorID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Kardex because Proveedor exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Kardex ON Kardex FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Kardex */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insNumMov char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Producto  Kardex on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00028b3d", PARENT_OWNER="", PARENT_TABLE="Producto"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_11", FK_COLUMNS="ProductoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(ProductoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Producto
        WHERE
          /* %JoinFKPK(inserted,Producto) */
          inserted.ProductoID = Producto.ProductoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Kardex because Producto does not exist.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Proveedor  Kardex on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Proveedor"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_16", FK_COLUMNS="ProveedorID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(ProveedorID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Proveedor
        WHERE
          /* %JoinFKPK(inserted,Proveedor) */
          inserted.ProveedorID = Proveedor.ProveedorID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Kardex because Proveedor does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Mesa ON Mesa FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Mesa */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Pedido  Mesa on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="000116cf", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Mesa"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_15", FK_COLUMNS="PedidoID" */
    IF EXISTS (SELECT * FROM deleted,Pedido
      WHERE
        /* %JoinFKPK(deleted,Pedido," = "," AND") */
        deleted.PedidoID = Pedido.PedidoID AND
        NOT EXISTS (
          SELECT * FROM Mesa
          WHERE
            /* %JoinFKPK(Mesa,Pedido," = "," AND") */
            Mesa.PedidoID = Pedido.PedidoID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Mesa because Pedido exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Mesa ON Mesa FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Mesa */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insMesaID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Pedido  Mesa on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00013700", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Mesa"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_15", FK_COLUMNS="PedidoID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Pedido
        WHERE
          /* %JoinFKPK(inserted,Pedido) */
          inserted.PedidoID = Pedido.PedidoID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Mesa because Pedido does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Metodo_Pago ON Metodo_Pago FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Metodo_Pago */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Comprobante_Pago  Metodo_Pago on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00013fb7", PARENT_OWNER="", PARENT_TABLE="Comprobante_Pago"
    CHILD_OWNER="", CHILD_TABLE="Metodo_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_14", FK_COLUMNS="ComPagID" */
    IF EXISTS (SELECT * FROM deleted,Comprobante_Pago
      WHERE
        /* %JoinFKPK(deleted,Comprobante_Pago," = "," AND") */
        deleted.ComPagID = Comprobante_Pago.ComPagID AND
        NOT EXISTS (
          SELECT * FROM Metodo_Pago
          WHERE
            /* %JoinFKPK(Metodo_Pago,Comprobante_Pago," = "," AND") */
            Metodo_Pago.ComPagID = Comprobante_Pago.ComPagID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Metodo_Pago because Comprobante_Pago exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Metodo_Pago ON Metodo_Pago FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Metodo_Pago */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insMetPagID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Comprobante_Pago  Metodo_Pago on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00015b4f", PARENT_OWNER="", PARENT_TABLE="Comprobante_Pago"
    CHILD_OWNER="", CHILD_TABLE="Metodo_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_14", FK_COLUMNS="ComPagID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(ComPagID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Comprobante_Pago
        WHERE
          /* %JoinFKPK(inserted,Comprobante_Pago) */
          inserted.ComPagID = Comprobante_Pago.ComPagID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Metodo_Pago because Comprobante_Pago does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Pedido ON Pedido FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Pedido */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Pedido  Comprobante_Pago on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0004e268", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_13", FK_COLUMNS="PedidoID" */
    IF EXISTS (
      SELECT * FROM deleted,Comprobante_Pago
      WHERE
        /*  %JoinFKPK(Comprobante_Pago,deleted," = "," AND") */
        Comprobante_Pago.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Pedido because Comprobante_Pago exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Pedido  Mesa on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Mesa"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_15", FK_COLUMNS="PedidoID" */
    IF EXISTS (
      SELECT * FROM deleted,Mesa
      WHERE
        /*  %JoinFKPK(Mesa,deleted," = "," AND") */
        Mesa.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Pedido because Mesa exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Pedido  Detalle_Pedido on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Detalle_Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_20", FK_COLUMNS="PedidoID" */
    IF EXISTS (
      SELECT * FROM deleted,Detalle_Pedido
      WHERE
        /*  %JoinFKPK(Detalle_Pedido,deleted," = "," AND") */
        Detalle_Pedido.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Pedido because Detalle_Pedido exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Pedido  Comprobante_Pago on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_21", FK_COLUMNS="PedidoID" */
    IF EXISTS (
      SELECT * FROM deleted,Comprobante_Pago
      WHERE
        /*  %JoinFKPK(Comprobante_Pago,deleted," = "," AND") */
        Comprobante_Pago.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Pedido because Comprobante_Pago exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Cliente  Pedido on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Cliente"
    CHILD_OWNER="", CHILD_TABLE="Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_9", FK_COLUMNS="ClienteID" */
    IF EXISTS (SELECT * FROM deleted,Cliente
      WHERE
        /* %JoinFKPK(deleted,Cliente," = "," AND") */
        deleted.ClienteID = Cliente.ClienteID AND
        NOT EXISTS (
          SELECT * FROM Pedido
          WHERE
            /* %JoinFKPK(Pedido,Cliente," = "," AND") */
            Pedido.ClienteID = Cliente.ClienteID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Pedido because Cliente exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Pedido ON Pedido FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Pedido */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insPedidoID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Pedido  Comprobante_Pago on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="000579df", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_13", FK_COLUMNS="PedidoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Comprobante_Pago
      WHERE
        /*  %JoinFKPK(Comprobante_Pago,deleted," = "," AND") */
        Comprobante_Pago.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Pedido because Comprobante_Pago exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Pedido  Mesa on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Mesa"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_15", FK_COLUMNS="PedidoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Mesa
      WHERE
        /*  %JoinFKPK(Mesa,deleted," = "," AND") */
        Mesa.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Pedido because Mesa exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Pedido  Detalle_Pedido on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Detalle_Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_20", FK_COLUMNS="PedidoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Detalle_Pedido
      WHERE
        /*  %JoinFKPK(Detalle_Pedido,deleted," = "," AND") */
        Detalle_Pedido.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Pedido because Detalle_Pedido exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Pedido  Comprobante_Pago on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Pedido"
    CHILD_OWNER="", CHILD_TABLE="Comprobante_Pago"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_21", FK_COLUMNS="PedidoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(PedidoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Comprobante_Pago
      WHERE
        /*  %JoinFKPK(Comprobante_Pago,deleted," = "," AND") */
        Comprobante_Pago.PedidoID = deleted.PedidoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Pedido because Comprobante_Pago exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Cliente  Pedido on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Cliente"
    CHILD_OWNER="", CHILD_TABLE="Pedido"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_9", FK_COLUMNS="ClienteID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(ClienteID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Cliente
        WHERE
          /* %JoinFKPK(inserted,Cliente) */
          inserted.ClienteID = Cliente.ClienteID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Pedido because Cliente does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Producto ON Producto FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Producto */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Producto  Kardex on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0001febe", PARENT_OWNER="", PARENT_TABLE="Producto"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_11", FK_COLUMNS="ProductoID" */
    IF EXISTS (
      SELECT * FROM deleted,Kardex
      WHERE
        /*  %JoinFKPK(Kardex,deleted," = "," AND") */
        Kardex.ProductoID = deleted.ProductoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Producto because Kardex exists.'
      GOTO ERROR
    END

    /* ERwin Builtin Trigger */
    /* Categoria  Producto on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Categoria"
    CHILD_OWNER="", CHILD_TABLE="Producto"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_19", FK_COLUMNS="CatID" */
    IF EXISTS (SELECT * FROM deleted,Categoria
      WHERE
        /* %JoinFKPK(deleted,Categoria," = "," AND") */
        deleted.CatID = Categoria.CatID AND
        NOT EXISTS (
          SELECT * FROM Producto
          WHERE
            /* %JoinFKPK(Producto,Categoria," = "," AND") */
            Producto.CatID = Categoria.CatID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Producto because Categoria exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Producto ON Producto FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Producto */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insProductoID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Producto  Kardex on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="00023948", PARENT_OWNER="", PARENT_TABLE="Producto"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_11", FK_COLUMNS="ProductoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(ProductoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Kardex
      WHERE
        /*  %JoinFKPK(Kardex,deleted," = "," AND") */
        Kardex.ProductoID = deleted.ProductoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Producto because Kardex exists.'
      GOTO ERROR
    END
  END

  /* ERwin Builtin Trigger */
  /* Categoria  Producto on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00000000", PARENT_OWNER="", PARENT_TABLE="Categoria"
    CHILD_OWNER="", CHILD_TABLE="Producto"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_19", FK_COLUMNS="CatID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(CatID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Categoria
        WHERE
          /* %JoinFKPK(inserted,Categoria) */
          inserted.CatID = Categoria.CatID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Producto because Categoria does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Proveedor ON Proveedor FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Proveedor */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Proveedor  Kardex on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0000ee0b", PARENT_OWNER="", PARENT_TABLE="Proveedor"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_16", FK_COLUMNS="ProveedorID" */
    IF EXISTS (
      SELECT * FROM deleted,Kardex
      WHERE
        /*  %JoinFKPK(Kardex,deleted," = "," AND") */
        Kardex.ProveedorID = deleted.ProveedorID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Proveedor because Kardex exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Proveedor ON Proveedor FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Proveedor */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insProveedorID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Proveedor  Kardex on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="0001025e", PARENT_OWNER="", PARENT_TABLE="Proveedor"
    CHILD_OWNER="", CHILD_TABLE="Kardex"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_16", FK_COLUMNS="ProveedorID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(ProveedorID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Kardex
      WHERE
        /*  %JoinFKPK(Kardex,deleted," = "," AND") */
        Kardex.ProveedorID = deleted.ProveedorID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Proveedor because Kardex exists.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Tipo_Rol ON Tipo_Rol FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Tipo_Rol */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Tipo_Rol  Empleado on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0000ecff", PARENT_OWNER="", PARENT_TABLE="Tipo_Rol"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_4", FK_COLUMNS="RolID" */
    IF EXISTS (
      SELECT * FROM deleted,Empleado
      WHERE
        /*  %JoinFKPK(Empleado,deleted," = "," AND") */
        Empleado.RolID = deleted.RolID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Tipo_Rol because Empleado exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Tipo_Rol ON Tipo_Rol FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Tipo_Rol */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insRolID char(2),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Tipo_Rol  Empleado on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="00010234", PARENT_OWNER="", PARENT_TABLE="Tipo_Rol"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_4", FK_COLUMNS="RolID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(RolID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Empleado
      WHERE
        /*  %JoinFKPK(Empleado,deleted," = "," AND") */
        Empleado.RolID = deleted.RolID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Tipo_Rol because Empleado exists.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Tipo_Usuario ON Tipo_Usuario FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Tipo_Usuario */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Tipo_Usuario  Usuario on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0000f0e0", PARENT_OWNER="", PARENT_TABLE="Tipo_Usuario"
    CHILD_OWNER="", CHILD_TABLE="Usuario"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_18", FK_COLUMNS="TipUsuID" */
    IF EXISTS (
      SELECT * FROM deleted,Usuario
      WHERE
        /*  %JoinFKPK(Usuario,deleted," = "," AND") */
        Usuario.TipUsuID = deleted.TipUsuID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Tipo_Usuario because Usuario exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Tipo_Usuario ON Tipo_Usuario FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Tipo_Usuario */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insTipUsuID char(2),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Tipo_Usuario  Usuario on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="0001052e", PARENT_OWNER="", PARENT_TABLE="Tipo_Usuario"
    CHILD_OWNER="", CHILD_TABLE="Usuario"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_18", FK_COLUMNS="TipUsuID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(TipUsuID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Usuario
      WHERE
        /*  %JoinFKPK(Usuario,deleted," = "," AND") */
        Usuario.TipUsuID = deleted.TipUsuID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Tipo_Usuario because Usuario exists.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Turno ON Turno FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Turno */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Turno  Empleado on parent delete no action */
    /* ERWIN_RELATION:CHECKSUM="0000e80b", PARENT_OWNER="", PARENT_TABLE="Turno"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_6", FK_COLUMNS="TurnoID" */
    IF EXISTS (
      SELECT * FROM deleted,Empleado
      WHERE
        /*  %JoinFKPK(Empleado,deleted," = "," AND") */
        Empleado.TurnoID = deleted.TurnoID
    )
    BEGIN
      SELECT @errno  = 30001,
             @errmsg = 'Cannot delete Turno because Empleado exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Turno ON Turno FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Turno */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insTurnoID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Turno  Empleado on parent update no action */
  /* ERWIN_RELATION:CHECKSUM="000100e6", PARENT_OWNER="", PARENT_TABLE="Turno"
    CHILD_OWNER="", CHILD_TABLE="Empleado"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_6", FK_COLUMNS="TurnoID" */
  IF
    /* %ParentPK(" OR",UPDATE) */
    UPDATE(TurnoID)
  BEGIN
    IF EXISTS (
      SELECT * FROM deleted,Empleado
      WHERE
        /*  %JoinFKPK(Empleado,deleted," = "," AND") */
        Empleado.TurnoID = deleted.TurnoID
    )
    BEGIN
      SELECT @errno  = 30005,
             @errmsg = 'Cannot update Turno because Empleado exists.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go




CREATE TRIGGER tD_Usuario ON Usuario FOR DELETE AS
/* ERwin Builtin Trigger */
/* DELETE trigger on Usuario */
BEGIN
  DECLARE  @errno   int,
           @errmsg  varchar(255)
    /* ERwin Builtin Trigger */
    /* Tipo_Usuario  Usuario on child delete no action */
    /* ERWIN_RELATION:CHECKSUM="0001281f", PARENT_OWNER="", PARENT_TABLE="Tipo_Usuario"
    CHILD_OWNER="", CHILD_TABLE="Usuario"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_18", FK_COLUMNS="TipUsuID" */
    IF EXISTS (SELECT * FROM deleted,Tipo_Usuario
      WHERE
        /* %JoinFKPK(deleted,Tipo_Usuario," = "," AND") */
        deleted.TipUsuID = Tipo_Usuario.TipUsuID AND
        NOT EXISTS (
          SELECT * FROM Usuario
          WHERE
            /* %JoinFKPK(Usuario,Tipo_Usuario," = "," AND") */
            Usuario.TipUsuID = Tipo_Usuario.TipUsuID
        )
    )
    BEGIN
      SELECT @errno  = 30010,
             @errmsg = 'Cannot delete last Usuario because Tipo_Usuario exists.'
      GOTO ERROR
    END


    /* ERwin Builtin Trigger */
    RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


CREATE TRIGGER tU_Usuario ON Usuario FOR UPDATE AS
/* ERwin Builtin Trigger */
/* UPDATE trigger on Usuario */
BEGIN
  DECLARE  @NUMROWS int,
           @nullcnt int,
           @validcnt int,
           @insUsuarioID char(18),
           @errno   int,
           @errmsg  varchar(255)

  SELECT @NUMROWS = @@rowcount
  /* ERwin Builtin Trigger */
  /* Tipo_Usuario  Usuario on child update no action */
  /* ERWIN_RELATION:CHECKSUM="00015245", PARENT_OWNER="", PARENT_TABLE="Tipo_Usuario"
    CHILD_OWNER="", CHILD_TABLE="Usuario"
    P2C_VERB_PHRASE="", C2P_VERB_PHRASE="", 
    FK_CONSTRAINT="R_18", FK_COLUMNS="TipUsuID" */
  IF
    /* %ChildFK(" OR",UPDATE) */
    UPDATE(TipUsuID)
  BEGIN
    SELECT @nullcnt = 0
    SELECT @validcnt = count(*)
      FROM inserted,Tipo_Usuario
        WHERE
          /* %JoinFKPK(inserted,Tipo_Usuario) */
          inserted.TipUsuID = Tipo_Usuario.TipUsuID
    /* %NotnullFK(inserted," IS NULL","select @nullcnt = count(*) from inserted where"," AND") */
    
    IF @validcnt + @nullcnt != @NUMROWS
    BEGIN
      SELECT @errno  = 30007,
             @errmsg = 'Cannot update Usuario because Tipo_Usuario does not exist.'
      GOTO ERROR
    END
  END


  /* ERwin Builtin Trigger */
  RETURN
ERROR:
    raiserror @errno @errmsg
    rollback transaction
END

go


