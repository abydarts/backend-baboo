PARAMETERS_JSON=$(aws ssm get-parameters-by-path --path $1 --with-decryption)
count=$(echo $PARAMETERS_JSON | jq '.Parameters | length')

filename_pattern=$1
i=0

while [ "$i" -lt $count ]
do
    filename=$(echo $PARAMETERS_JSON | jq -r '.Parameters['$i'].Name')
    filename=${filename/$filename_pattern/}
    value=$(echo $PARAMETERS_JSON | jq -r '.Parameters['$i'].Value')
    printf "%s\n" "$value" > $filename
    i=$(( i + 1 ))
done
