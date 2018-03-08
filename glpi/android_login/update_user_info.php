public function StoreUserInfo($name, $email, $password, $gender, $age) {
        $hash = $this->hashFunction($password);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt
 
        $stmt = $this->conn->prepare("INSERT INTO android_php_post(name, email, encrypted_password, salt, gender, age) VALUES(?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $encrypted_password, $salt, $gender, $age);
        $result = $stmt->execute();
        $stmt->close();
 
        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT name, email, encrypted_password, salt, gender, age FROM android_php_post WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt-> bind_result($token2,$token3,$token4,$token5,$token6,$token7);
 
            while ( $stmt-> fetch() ) {
               $user["name"] = $token2;
               $user["email"] = $token3;
               $user["gender"] = $token6;
               $user["age"] = $token7;
            }
            $stmt->close();
            return $user;
        } else {
          return false;
        }
    }
 
   public function hashFunction($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
	
	$stmt->bind_param("ssssss", $name, $email, $encrypted_password, $salt, $gender, $age);
	
public function VerifyUserAuthentication($email, $password) {
 
        $stmt = $this->conn->prepare("SELECT name, email, encrypted_password, salt, gender, age FROM android_php_post WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        if ($stmt->execute()) {
            $stmt-> bind_result($token2,$token3,$token4,$token5,$token6,$token7);
 
            while ( $stmt-> fetch() ) {
               $user["name"] = $token2;
               $user["email"] = $token3;
               $user["encrypted_password"] = $token4;
               $user["salt"] = $token5;
               $user["gender"] = $token6;
               $user["age"] = $token7;
            }
 
            $stmt->close();
 
            // verifying user password
            $salt = $token5;
            $encrypted_password = $token4;
            $hash = $this->CheckHashFunction($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $user;
            }
        } else {
            return NULL;
        }
    }
 
public function checkHashFunction($salt, $password) {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }
	
	
	
	public function CheckExistingUser($email) {
        $stmt = $this->conn->prepare("SELECT email from android_php_post WHERE email = ?");
 
        $stmt->bind_param("s", $email);
 
        $stmt->execute();
 
        $stmt->store_result();
 
        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }
    }