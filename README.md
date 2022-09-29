# salesforce-api

## How to create force api CI/CD Pipeline

Build phase:

- Create new branch and gitlab environment if necessary (development, qa, production)
  - Make sure the branch is protected or CI/CD Variables will be empty.
- Create ECR repository dev-ptf-force-api-backend
- Add gitlab-ci.yml to git repo root directory.
- Add environment variables to job from gitlab UI (Settings -> CI/CD -> Variables).
- Create new user dev-ptf-force-api-s3-agent with access key with S3 full access.
- Create a new bucket named dev-ptf-force-api (with settings copied from dev-ptk-motorist)
- Add relevant config to ssm parameter store.
  - use access key of dev-ptf-force-api-s3-agent for S3_KEY and S3_SECRET, and dev-ptf-force-api as S3_BUCKET.
  - use Firebase Web API key for FIREAPI_KEY, and Firebase Cloud Messaging Server Key for FIREBASE_KEY
- attach AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION to the Gitlab CI/CD env var with value from dev-ptf-deploy-agent (create a new access key if you don't have the existing access key)
- add permission to the IAM user dev-ptf-deploy-agent based on the error from CI/CD job

Deploy phase

- Don't forget to tag every new resources with:
  - STAGE:dev
  - PROJECT:PTF
- Prepare Fargate in ECS
  - Create cluster dev-ptf-force-api-backend-cluster (without creating new VPC, without CloudWatch Container Insights) (add tags)
  - Create task definition dev-ptf-force-api-backend-task
    - Use task role devPtfEcsTasksExecutionRole
    - use Linux OS
    - Add container (without private repository authentication, use mock value for image)
  - Create security group dev-ptf-force-api-backend-lb-sg
    - Use dev ptf vpc
    - inbound traffic: allow all http 80 and https 443 from all ipv4 and ipv6
    - outbound traffic: allow all trafic from all ipv4 and ipv6
  - Create Target Group
    - Use dev ptf vpc
    - Use IP as Target Type. Don't register any
    - add code 405 as success codes in health check
  - Create Application Load Balancer dev-ptf-force-api-backend-alb
    - Use public subnet in ptf vpc
    - Use previously created security group
  - Create ECS service
    - Use public subnet in ptf vpc
    - Use ALB
    - Add container to load balancer with listener port of 80:HTTP
    - Don't edit security group
    - the suffix Update in dev-ptf-force-api-backendUpdate is because we change the ELB from ALB to NLB.
      future services should not be prefixed with Update.
    - Associate this security group as inbound rule to RDS security group (check from RDS > Database > Security group rules)

Connect to HTTPS domain

- Login to pintap infra account
- Update pintap hosted zone and include link to load balancer
- Associate existing ACM certificate for HTTPS
  - https://aws.amazon.com/premiumsupport/knowledge-center/associate-acm-certificate-alb-nlb/
