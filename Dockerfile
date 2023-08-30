# Usa la imagen oficial de Python basada en Alpine
FROM python:3-alpine

# Establece el directorio de trabajo dentro del contenedor
WORKDIR /app

# Instala las dependencias necesarias
RUN apk update && \
    apk add --no-cache g++ fpc mono openjdk11 python3 py3-pip ruby && \
    pip install --no-cache-dir pymysql

# Crea un usuario no privilegiado y cambia al usuario
RUN adduser -D appuser
USER appuser

# Crea directorios para datos persistentes
RUN mkdir /app/data /app/logs

# Configura el prompt del contenedor
RUN echo PS1="\[\e[1;32m\]\u@\h:\w\[\e[m\]\$ " >> /etc/profile

# Copia el código del servidor de Python al directorio de trabajo
COPY judge.py /app/judge.py

# Usa volúmenes para montar directorios persistentes
VOLUME ["/app/data", "/app/logs"]

# Expone el puerto en el que se ejecutará el servidor (ajústelo según sea necesario)
EXPOSE 8000

# Ejecuta el servidor de Python al iniciar el contenedor
CMD ["python", "judge.py", "-judge"]
