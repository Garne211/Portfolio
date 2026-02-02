import paramiko
import time

# Configuration SSH
hostname = "137.74.41.200"
username = "debian"
password = "Lesperseverants1."

# Fichier local à transférer
local_file = r"c:\wamp64\www\Portfolio\public\assets\css\style.css"
remote_file = "/tmp/style-new.css"

try:
    # Connexion SSH
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(hostname, username=username, password=password)
    
    # Transfert SFTP
    sftp = ssh.open_sftp()
    sftp.put(local_file, remote_file)
    sftp.close()
    
    print(f"Fichier transféré: {remote_file}")
    
    # Commandes à exécuter
    commands = [
        "sudo cp /var/www/portfolio/public/assets/css/style.css /var/www/portfolio/public/assets/css/style.css.backup",
        "sudo mv /tmp/style-new.css /var/www/portfolio/public/assets/css/style.css",
        "sudo chmod 644 /var/www/portfolio/public/assets/css/style.css",
        "NEW_VERSION=$(date +%s); sudo sed -i \"s/style\\.css?v=[0-9]*/style.css?v=$NEW_VERSION/g\" /var/www/portfolio/public/index.php; echo \"Version CSS: $NEW_VERSION\""
    ]
    
    for cmd in commands:
        stdin, stdout, stderr = ssh.exec_command(cmd)
        if "sudo" in cmd:
            stdin.write(password + "\n")
            stdin.flush()
        
        output = stdout.read().decode()
        error = stderr.read().decode()
        
        if output:
            print(output.strip())
        if error and "sudo" not in error.lower():
            print(f"Erreur: {error.strip()}")
    
    ssh.close()
    print("\nMise à jour terminée!")
    
except Exception as e:
    print(f"Erreur: {e}")
