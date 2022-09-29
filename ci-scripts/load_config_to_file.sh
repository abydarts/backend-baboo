PARAMETERS_JSON=$(aws ssm get-parameters-by-path --path $1 --with-decryption)
KEY_VALUE_PAIRS=$(echo $PARAMETERS_JSON | jq '.Parameters|map("\(.Name)=\(.Value)\n")')

touch $2
echo $KEY_VALUE_PAIRS | jq -r .[] > $2
sed -i '/^$/d' $2
sed -i 's#/dev/ptf_force_api/config/##g' $2
