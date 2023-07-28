# Use the official Python image as the base image
FROM python:3

# Set the working directory inside the container
COPY judge.py /app/judge.py

# Copy the server code and other necessary files to the container
WORKDIR /app

# Install dependencies (if required)
RUN pip install pymysql

# Expose the port your Python server is running on (modify the port if needed)
EXPOSE 8000

# Run the Python server
CMD ["python", "judge.py", "-judge"]
