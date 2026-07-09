function mostrarDialogoExito(mensaje) {
    $("#dialogoExito p").text(mensaje);
    $("#dialogoExito").dialog({
        modal: true,
        buttons: {
            "Aceptar": function () {
                $(this).dialog("close");
            }
        }
    });
}

function mostrarDialogoError(mensaje) {
    $("#dialogoError p").text(mensaje);
    $("#dialogoError").dialog({
        modal: true,
        buttons: {
            "Aceptar": function () {
                $(this).dialog("close");
            }
        }
    });
}

function confirmarEliminacion(callback) {
    $("#dialogoConfirmar").dialog({
        modal: true,
        buttons: {
            "Eliminar": function () {
                $(this).dialog("close");
                callback();
            },
            "Cancelar": function () {
                $(this).dialog("close");
            }
        }
    });
}

let paginaActual = 1;

$(document).ready(function () {
    listarUsuarios();

    $("#busqueda").on("keyup", function () {
        paginaActual = 1;
        listarUsuarios();
    });

    $("#limite").on("change", function () {
        paginaActual = 1;
        listarUsuarios();
    });

    $("#orden").on("change", function () {
        paginaActual = 1;
        listarUsuarios();
    });
});

function listarUsuarios() {
    let busqueda = $("#busqueda").val();
    let limite = $("#limite").val();
    let orden = $("#orden").val();

    $.ajax({
        url: "usuarios.php",
        type: "POST",
        dataType: "json",
        data: {
            accion: "listar",
            busqueda: busqueda,
            limite: limite,
            orden: orden,
            pagina: paginaActual
        },
        success: function (respuesta) {
            mostrarTabla(respuesta.usuarios);
            mostrarTotal(respuesta.total);
            crearPaginacion(respuesta.total, limite);
        },
        error: function () {
            mostrarDialogoError("Ocurrió un error al consultar los usuarios.");
        }
    });
}

function mostrarTabla(usuarios) {
    let filas = "";

    if (usuarios.length === 0) {
        filas = `
            <tr>
                <td colspan="6">No se encontraron registros.</td>
            </tr>
        `;
    } else {
        usuarios.forEach(function (usuario) {
            filas += `
                <tr>
                    <td>${usuario.id}</td>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.correo}</td>
                    <td>${usuario.telefono}</td>
                    <td>${usuario.ciudad}</td>
                    <td>
                        <button class="btnEditar"
                            data-id="${usuario.id}"
                            data-nombre="${usuario.nombre}"
                            data-correo="${usuario.correo}"
                            data-telefono="${usuario.telefono}"
                            data-ciudad="${usuario.ciudad}">
                            Editar
                        </button>

                        <button class="btnEliminar"
                            data-id="${usuario.id}">
                            Eliminar
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    $("#tablaUsuarios").hide();
    $("#tablaUsuarios").html(filas);
    $("#tablaUsuarios").fadeIn(600);
}

function mostrarTotal(total) {
    $("#totalRegistros").text("Total de registros: " + total);
}

function crearPaginacion(total, limite) {
    let totalPaginas = Math.ceil(total / limite);
    let botones = "";

    for (let i = 1; i <= totalPaginas; i++) {
        if (i === paginaActual) {
            botones += `<button class="pagina activa" data-pagina="${i}">${i}</button>`;
        } else {
            botones += `<button class="pagina" data-pagina="${i}">${i}</button>`;
        }
    }

    $("#paginacion").html(botones);
}

$(document).on("click", ".pagina", function () {
    paginaActual = parseInt($(this).data("pagina"));
    listarUsuarios();
});
// Guardar usuario (Agregar o Editar)

$("#btnGuardar").on("click", function () {

    let id = $("#idUsuario").val();
    let nombre = $("#nombre").val().trim();
    let correo = $("#correo").val().trim();
    let telefono = $("#telefono").val().trim();
    let ciudad = $("#ciudad").val().trim();

    let expresionNombre = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,}$/;
    let expresionCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let expresionTelefono = /^\d{10}$/;

    if (!expresionNombre.test(nombre)) {
        mostrarDialogoError("Ingrese un nombre válido. Solo letras y espacios.");
        $("#nombre").focus();
        return;
    }

    if (!expresionCorreo.test(correo)) {
        mostrarDialogoError("Ingrese un correo electrónico válido.");
        $("#correo").focus();
        return;
    }

    if (!expresionTelefono.test(telefono)) {
        mostrarDialogoError("El teléfono debe contener exactamente 10 dígitos.");
        $("#telefono").focus();
        return;
    }

    if (ciudad === "") {
        mostrarDialogoError("Ingrese la ciudad.");
        $("#ciudad").focus();
        return;
    }

    let accion = id === "" ? "agregar" : "editar";

    $.ajax({
        url: "usuarios.php",
        type: "POST",
        dataType: "json",
        data: {
            accion: accion,
            id: id,
            nombre: nombre,
            correo: correo,
            telefono: telefono,
            ciudad: ciudad
        },
        success: function (respuesta) {

            if (respuesta.respuesta === "ok") {

                limpiarFormulario();

                $("#btnGuardar").text("Guardar");

                listarUsuarios();

                if (accion === "agregar") {
                    mostrarDialogoExito("Usuario agregado correctamente.");
                } else {
                    mostrarDialogoExito("Usuario actualizado correctamente.");
                }

            } else {
                mostrarDialogoError(respuesta.mensaje || "Ocurrió un error al guardar el usuario.");
            }

        },
        error: function () {
            mostrarDialogoError("No se pudo comunicar con el servidor.");
        }
    });

});


// Editar usuario

$(document).on("click", ".btnEditar", function () {

    $("#idUsuario").val($(this).data("id"));

    $("#nombre").val($(this).data("nombre"));

    $("#correo").val($(this).data("correo"));

    $("#telefono").val($(this).data("telefono"));

    $("#ciudad").val($(this).data("ciudad"));

    $("#btnGuardar").text("Actualizar");

});


// Eliminar usuario

$(document).on("click", ".btnEliminar", function () {

    let boton = $(this);
    let fila = boton.closest("tr");
    let id = boton.data("id");

    confirmarEliminacion(function () {

        $.ajax({
            url: "usuarios.php",
            type: "POST",
            dataType: "json",
            data: {
            accion: "eliminar",
            id: id
            },
            success: function (respuesta) {

                if (respuesta.respuesta === "ok") {

                    fila.fadeOut(600, function () {
                        listarUsuarios();
                    });

                    mostrarDialogoExito("Usuario eliminado correctamente.");

                } else {

                    mostrarDialogoError("No fue posible eliminar el registro.");

                }

            },
            error: function () {
                mostrarDialogoError("Ocurrió un error al eliminar el usuario.");
            }
        });

    });

});


// Limpiar formulario

$("#btnLimpiar").on("click", function () {

    limpiarFormulario();

});


function limpiarFormulario() {

    $("#idUsuario").val("");

    $("#nombre").val("");

    $("#correo").val("");

    $("#telefono").val("");

    $("#ciudad").val("");

    $("#btnGuardar").text("Guardar");

}